<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireWebhookSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is(trim(config('cashier.path', 'stripe'), '/').'/webhook') && ! config('cashier.webhook.secret')) {
            abort(503, 'Stripe webhook verification is not configured.');
        }

        return $next($request);
    }
}
