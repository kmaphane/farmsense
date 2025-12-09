<?php

use App\Http\Controllers\Batches\BatchAnalyticsController;
use App\Http\Controllers\Batches\BatchController;
use App\Http\Controllers\Batches\DailyLogController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveSales\LiveSaleController;
use App\Http\Controllers\Portioning\PortioningController;
use App\Http\Controllers\Products\ProductPricingController;
use App\Http\Controllers\Slaughter\SlaughterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('welcome'))->name('home');

// All authenticated routes are now in Filament at /admin
// Settings, dashboard, and admin functionality moved to Filament

/*
|--------------------------------------------------------------------------
| Field Mode Routes (for field workers)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Batch management
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');

    // Daily log management
    Route::get('/batches/{batch}/logs/create', [DailyLogController::class, 'create'])->name('batches.logs.create');
    Route::post('/batches/{batch}/logs', [DailyLogController::class, 'store'])->name('batches.logs.store');
    Route::get('/batches/{batch}/logs/{dailyLog}/edit', [DailyLogController::class, 'edit'])->name('batches.logs.edit');
    Route::put('/batches/{batch}/logs/{dailyLog}', [DailyLogController::class, 'update'])->name('batches.logs.update');

    // Batch analytics (JSON endpoints for charts)
    Route::prefix('/batches/{batch}/analytics')->name('batches.analytics.')->group(function () {
        Route::get('/feed', [BatchAnalyticsController::class, 'feedConsumption'])->name('feed');
        Route::get('/mortality', [BatchAnalyticsController::class, 'mortalityTrend'])->name('mortality');
        Route::get('/environment', [BatchAnalyticsController::class, 'environmentalData'])->name('environment');
        Route::get('/summary', [BatchAnalyticsController::class, 'summary'])->name('summary');
    });

    // Live sales (Sheet-based from batch show page)
    Route::post('/batches/{batch}/live-sale', [LiveSaleController::class, 'store'])->name('live-sales.store');

    // Slaughter management (JSON endpoint for Quick Actions sheet data)
    Route::get('/api/slaughter/data', [SlaughterController::class, 'data'])->name('slaughter.data');
    Route::post('/slaughter', [SlaughterController::class, 'store'])->name('slaughter.store');

    // Portioning management (JSON endpoint for Quick Actions sheet data)
    Route::get('/api/portioning/data', [PortioningController::class, 'data'])->name('portioning.data');
    Route::post('/portioning', [PortioningController::class, 'store'])->name('portioning.store');

    // Batch management (JSON endpoints for Quick Actions sheet)
    Route::get('/api/batches/data', [BatchController::class, 'data'])->name('batches.data');
    Route::post('/api/batches/store', [BatchController::class, 'store'])->name('batches.store');

    // Customer management (JSON endpoints for Quick Actions sheet)
    Route::get('/api/customers/data', [CustomerController::class, 'data'])->name('customers.data');
    Route::post('/api/customers/store', [CustomerController::class, 'store'])->name('customers.store');

    // Product pricing
    Route::get('/products/pricing', [ProductPricingController::class, 'index'])->name('products.pricing');
    Route::patch('/products/{product}/price', [ProductPricingController::class, 'update'])->name('products.update-price');
});
