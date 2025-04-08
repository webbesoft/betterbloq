<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $exemptRoutes = ['/login', '/register', '/checkout'];
        $isPlansPage = ($request->getRequestUri() === '/plans' || $request->getRequestUri() === '/checkout');
        $cacheKeyPrefix = 'user_subscription_';
        $cacheDuration = 3600;

        if (in_array($request->getRequestUri(), $exemptRoutes)) {
            return $next($request);
        }

        if ($request->user()) {
            $userId = $request->user()->id;
            $cacheKey = $cacheKeyPrefix.$userId;

            $isSubscribed = Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
                return $request->user()->subscribed('standard');
            });

            if ($isPlansPage) {
                // On the plans page
                if ($isSubscribed) {
                    // User is already subscribed, redirect them to the dashboard
                    return redirect()->route('dashboard')->with('info', 'You already have an active subscription.');
                }

                // User is not subscribed, allow access to the plans page
                return $next($request);
            } else {
                // Not on the plans page
                if (! $isSubscribed) {
                    // User is not subscribed, redirect them to the plans page
                    return redirect()->route('plans.index')->with('warning', 'Please choose a subscription plan to continue.');
                }

                // User is subscribed, allow access to the requested page
                return $next($request);
            }
        } else {
            return redirect()->route('login')->with('warning', 'Please log in to access this page.');
        }
    }
}
