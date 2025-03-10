<?php

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
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // BulkBuy app routes
    Route::domain(config('app.app_urls.bulkbuy'))->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('shop/dashboard');
        })->name('buy-dashboard');

        Route::get('market', function () {
            return Inertia::render('shop/market');
        })->name('buy-market');
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
