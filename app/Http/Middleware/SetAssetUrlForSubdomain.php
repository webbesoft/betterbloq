<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SetAssetUrlForSubdomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
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
