<?php

namespace App\Services;

use App\Listeners\StripeEventListener;
use App\Models\CheckoutAttempt;
use App\Payments\StripeGateway;
use Laravel\Cashier\Events\WebhookReceived;

class CheckoutReconciliationService
{
    public function reconcile(CheckoutAttempt $attempt): void
    {
        $gateway = app(StripeGateway::class);
        $session = $attempt->stripe_session_id
            ? $gateway->session($attempt->stripe_session_id)
            : $gateway->findSession($attempt->stripe_payload['customer'], $attempt->id, $attempt->created_at->timestamp);
        if (! $session) {
            if ($attempt->expires_at->isPast()) {
                CheckoutAttempt::whereKey($attempt->id)->where('status', 'creating')->update(['status' => 'expired']);
            }

            return;
        }
        CheckoutAttempt::whereKey($attempt->id)->update(['stripe_session_id' => $session['id'], 'url' => $session['url'] ?? $attempt->url]);
        if (($session['status'] ?? '') === 'open') {
            CheckoutAttempt::whereKey($attempt->id)->where('status', 'creating')->update(['status' => 'open']);

            return;
        }
        $type = ($session['status'] ?? '') === 'expired' ? 'checkout.session.expired' : 'checkout.session.completed';
        app(StripeEventListener::class)->handle(new WebhookReceived(['type' => $type, 'data' => ['object' => $session]]));
    }
}
