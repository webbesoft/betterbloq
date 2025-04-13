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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $exemptRoutes = ['/login', '/register', '/checkout'];

        $isPlansRoute = $request->routeIs('plans.index');
        $isCheckoutRoute = $request->routeIs('checkout');
        $cacheKeyPrefix = 'user_subscription_status_';
        $cacheDuration = 3600;

        if (in_array($request->getRequestUri(), $exemptRoutes)) {
            return $next($request);
        }

        if ($request->user()) {
            $user = $request->user();
            $userId = $user->id;
            $cacheKey = $cacheKeyPrefix.$userId;

            $hasActiveSubscription = Cache::remember($cacheKey, $cacheDuration, function () use ($user) {
                return $user->subscriptions()->active()->exists();
            });

            if ($isPlansRoute || $isCheckoutRoute) {
                if ($hasActiveSubscription) {
                    return redirect()->route('dashboard')->with('info', 'You already have an active subscription.');
                }

                return $next($request);
            }

            if (! $hasActiveSubscription) {
                return redirect()->route('plans.index')->with('warning', 'Please choose a subscription plan to continue.');
            }

            return $next($request);

        } else {
            return redirect()->route('login')->with('warning', 'Please log in to access this page.');
        }
    }
}
