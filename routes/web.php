<?php

use App\Apps\BulkBuy\Controllers\OrderController;
use App\Apps\BulkBuy\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::domain(config('app.app_urls.bulkbuy'))->group(function () {
    Route::get('/', function () {
        return Inertia::render('shop/landing');
    })->name('buy-landing');
});

Route::domain('')->group(function () {
    Route::get('/', function () {
        return Inertia::render('welcome');
    })->name('home');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // BulkBuy app routes
    Route::domain(config('app.app_urls.bulkbuy'))->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('shop/dashboard');
        })->name('buy-dashboard');

        // market
        Route::get('market', [ProductController::class, 'index'])->name('buy-market');
        Route::get('market/product/{product}', [ProductController::class, 'show'])->name('buy-product');
        // products
        Route::post('products', [OrderController::class, 'store'])->name('buy-product.store');
    });

    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
