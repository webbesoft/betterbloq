<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StripeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('checkout', [CheckoutController::class, 'create'])->name('checkout.create');

Route::post('stripe/webhook', [StripeController::class, 'handle'])->name('stripe.handle');
