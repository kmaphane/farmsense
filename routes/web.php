<?php

use App\Http\Controllers\Batches\BatchAnalyticsController;
use App\Http\Controllers\Batches\BatchController;
use App\Http\Controllers\Batches\DailyLogController;
use App\Http\Controllers\DashboardController;
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
});
