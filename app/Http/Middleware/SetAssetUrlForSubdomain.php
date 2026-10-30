<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SetAssetUrlForSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // if ($request->getHost() === 'admin.localhost' || $request->getHost() === 'admin.localhost:8000') {
        //     Config::set('app.asset_url', 'http://admin.localhost:8000');
        // } else {
        //     // Set the default APP_URL for your main domain if needed
        //     Config::set('app.asset_url', 'http://localhost:8000');
        // }
        if ($request->getHost() === config('app.app_urls.bulkbuy')) {
            URL::forceScheme($request->getScheme()); // Ensure the scheme (http/https) is correct
            URL::forceRootUrl($request->getScheme().'://'.$request->getHost());
        }

        return $next($request);
    }
}
