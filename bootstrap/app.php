<?php

use App\Http\Middleware\CheckUserSubscription;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
// use App\Jobs\FinalizePurchasePoolJob;
// use App\Models\Log;
// use App\Models\PurchasePool;
// use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance']);
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web()->alias([
            'subscribed' => CheckUserSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        Integration::handles($exceptions);
    })
    // ->withSchedule(function (Schedule $schedule) {

    //     $schedule->call(function () {
    //         $poolsToClose = PurchasePool::where('status', 'active')
    //             ->where('end_date', '<=', now())
    //             ->get();
    //         foreach ($poolsToClose as $pool) {
    //             Log::info("Dispatching FinalizePurchasePoolJob for Pool ID: {$pool->id}");
    //             FinalizePurchasePoolJob::dispatch($pool);
    //             // update pool status immediately to 'closing' to prevent new orders
    //             $pool->update(['status' => 'closing']);
    //         }
    //     })->everyMinute();
    // })

    ->create();
