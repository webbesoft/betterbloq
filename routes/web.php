<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchasePoolController;
use App\Http\Controllers\PurchasePoolRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('shop/landing');
})->name('landing');

Route::middleware(['auth', 'verified'])->group(function () {
    // BulkBuy app routes
    Route::middleware(['subscribed'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // market
        Route::get('market', [ProductController::class, 'index'])->name('market');
        //        products
        Route::get('market/product/{product}', [ProductController::class, 'show'])->name('product.show');
        // orders
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        //        Route::get('orders', [OrderController::class, 'create'])->name('orders.create');
        // purchase pools
        Route::get('purchase-pools', [PurchasePoolController::class, 'index'])->name('purchase-pools.index');
        Route::get('purchase-pools/{purchase-pool}', [PurchasePoolController::class, 'show'])->name('purchase-pools.show');
        Route::post('purchase-pools', [PurchasePoolController::class, 'store'])->name('purchase-pools.store');
        // purchase pool requests
        Route::post('purchase-pool-requests', [PurchasePoolRequestController::class, 'store'])->name('purchase-pool-requests.store');

        Route::resource('projects', ProjectController::class);
    });

    Route::prefix('')->group(function () {
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    });

    // Route::get('dashboard', function () {
    //     return Inertia::render('dashboard');
    // })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
