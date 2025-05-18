<?php

use App\Http\Controllers\Prezet\ImageController;
use App\Http\Controllers\Prezet\IndexController;
use App\Http\Controllers\Prezet\OgimageController;
use App\Http\Controllers\Prezet\SearchController;
use App\Http\Controllers\Prezet\ShowController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::withoutMiddleware([
    ShareErrorsFromSession::class,
    StartSession::class,
    VerifyCsrfToken::class,
])
    ->group(function () {
        Route::get('docs/search', SearchController::class)->name('prezet.search');

        Route::get('docs/img/{path}', ImageController::class)
            ->name('docs.image')
            ->where('path', '.*');

        Route::get('/docs/ogimage/{slug}', OgimageController::class)
            ->name('docs.ogimage')
            ->where('slug', '.*');

        Route::get('docs', IndexController::class)
            ->name('prezet.index');

        Route::get('docs/{slug}', ShowController::class)
            ->name('prezet.show')
            ->where('slug', '.*'); // https://laravel.com/docs/11.x/routing#parameters-encoded-forward-slashes
    });
