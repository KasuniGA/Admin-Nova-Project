<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PriceTrackerController;

// All routes are prefixed with /nova-vendor/price-tracker
// Apply rate limiting to prevent abuse

Route::middleware(['throttle:60,1'])->group(function () {
    // Products and prices CRUD
    Route::get('/products', [PriceTrackerController::class, 'getProducts']);
    Route::get('/prices', [PriceTrackerController::class, 'index']);
    Route::get('/prices/{productId}', [PriceTrackerController::class, 'getProductHistory']);
    Route::post('/prices', [PriceTrackerController::class, 'store']);
    Route::put('/prices/{id}', [PriceTrackerController::class, 'update']);
    Route::delete('/prices/{id}', [PriceTrackerController::class, 'destroy']);

    // Dashboard stats and analytics
    Route::get('/stats', [PriceTrackerController::class, 'getStats']);
    Route::get('/recent-changes', [PriceTrackerController::class, 'getRecentChanges']);
    Route::get('/trend/{productId}', [PriceTrackerController::class, 'getProductTrend']);
});

// More restrictive rate limiting for tracking operations
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/track-all', [PriceTrackerController::class, 'trackAllPrices']);
    Route::post('/track/{productId}', [PriceTrackerController::class, 'trackProductPrice']);
    Route::post('/fetch-price', [PriceTrackerController::class, 'fetchPrice']);
});