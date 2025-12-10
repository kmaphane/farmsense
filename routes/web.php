<?php

use App\Http\Controllers\Batches\BatchAnalyticsController;
use App\Http\Controllers\Batches\BatchController;
use App\Http\Controllers\Batches\DailyLogController;
use App\Http\Controllers\CRM\CustomerController;
use App\Http\Controllers\CRM\SupplierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\WarehouseController;
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
    // Products index (user-facing)
    Route::get('/inventory/products', [\App\Http\Controllers\Inventory\ProductController::class, 'index'])->name('products.index');
    // Dashboard
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Batch management
    Route::get('/batches', [BatchController::class, 'index'])->name('batches.index');
    Route::get('/batches/history', [BatchController::class, 'history'])->name('batches.history');
    Route::get('/batches/{batch}', [BatchController::class, 'show'])->name('batches.show');

    // Daily log management
    Route::get('/daily-logs', [DailyLogController::class, 'index'])->name('daily-logs.index');
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

    // Live sales management
    Route::get('/live-sales', [LiveSaleController::class, 'index'])->name('live-sales.index');
    Route::get('/live-sales/{liveSale}', [LiveSaleController::class, 'show'])->name('live-sales.show');
    Route::post('/batches/{batch}/live-sale', [LiveSaleController::class, 'store'])->name('live-sales.store');

    // Slaughter management
    Route::get('/slaughter', [SlaughterController::class, 'index'])->name('slaughter.index');
    Route::get('/slaughter/{slaughter}', [SlaughterController::class, 'show'])->name('slaughter.show');
    Route::get('/api/slaughter/data', [SlaughterController::class, 'data'])->name('slaughter.data');
    Route::post('/slaughter', [SlaughterController::class, 'store'])->name('slaughter.store');

    // Portioning management
    Route::get('/portioning', [PortioningController::class, 'index'])->name('portioning.index');
    Route::get('/portioning/{portioning}', [PortioningController::class, 'show'])->name('portioning.show');
    Route::get('/api/portioning/data', [PortioningController::class, 'data'])->name('portioning.data');
    Route::post('/portioning', [PortioningController::class, 'store'])->name('portioning.store');

    // Batch management (JSON endpoints for Quick Actions sheet)
    Route::get('/api/batches/data', [BatchController::class, 'data'])->name('batches.data');
    Route::post('/api/batches/store', [BatchController::class, 'store'])->name('batches.store');

    // Customer management
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::get('/api/customers/data', [CustomerController::class, 'data'])->name('customers.data');
    Route::post('/api/customers/store', [CustomerController::class, 'store'])->name('customers.store');

    // Product pricing
    Route::get('/products/pricing', [ProductPricingController::class, 'index'])->name('products.pricing');
    Route::patch('/products/{product}/price', [ProductPricingController::class, 'update'])->name('products.update-price');

    // Stock movements management
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::get('/stock-movements/{stockMovement}', [StockMovementController::class, 'show'])->name('stock-movements.show');

    // Warehouse management
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');

    // Supplier management
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');

    // Product creation (user-facing)
    Route::get('/inventory/products/create', [\App\Http\Controllers\Inventory\ProductController::class, 'create'])->name('products.create');
    Route::post('/inventory/products', [\App\Http\Controllers\Inventory\ProductController::class, 'store'])->name('products.store');
});
