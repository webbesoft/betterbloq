<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProjectController;
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
            Route::get('market/product/{product}', [ProductController::class, 'show'])->name('product.show');
            // products
            Route::post('products', [OrderController::class, 'store'])->name('product.store');

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
