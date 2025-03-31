<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        $isPlansPage = ($request->getRequestUri() === '/plans' || $request->getRequestUri() === '/checkout');

        if (in_array($request->getRequestUri(), $exemptRoutes)) {
            return $next($request);
        }

        if ($request->user()) {
            // User is authenticated
            if ($isPlansPage) {
                // On the plans page
                if ($request->user()->subscribed('standard')) {
                    // User is already subscribed, redirect them to the dashboard or another appropriate page
                    return redirect()->route('buy-dashboard')->with('info', 'You already have an active subscription.');
                }

                // User is not subscribed, allow access to the plans page
                return $next($request);
            } else {
                // Not on the plans page
                if (! $request->user()->subscribed('standard')) {
                    // User is not subscribed, redirect them to the plans page
                    return redirect()->route('buy-plans')->with('warning', 'Please choose a subscription plan to continue.');
                }

                // User is subscribed, allow access to the requested page
                return $next($request);
            }
        } else {
            return redirect()->route('login')->with('warning', 'Please log in to access this page.');
        }
    }
}
