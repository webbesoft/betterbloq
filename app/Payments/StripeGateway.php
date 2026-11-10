<?php

namespace App\Payments;

use App\Models\User;
use Laravel\Cashier\Cashier;
use Stripe\Collection;

class StripeGateway
{
    public function customer(User $user): string
    {
        $user->refresh();
        if ($user->stripe_id) {
            return $user->stripe_id;
        }
        $customer = Cashier::stripe()->customers->create([
            'metadata' => ['user_id' => (string) $user->id],
        ], ['idempotency_key' => 'customer_'.$user->id]);
        $user->forceFill(['stripe_id' => $customer->id])->save();

        return $customer->id;
    }

    public function createSession(array $payload, string $key): array
    {
        return Cashier::stripe()->checkout->sessions->create($payload, ['idempotency_key' => $key])->toArray();
    }

    public function session(string $id): array
    {
        return Cashier::stripe()->checkout->sessions->retrieve($id)->toArray();
    }

    public function lineItems(string $id): Collection
    {
        $page = Cashier::stripe()->checkout->sessions->allLineItems($id, [
            'limit' => 100, 'expand' => ['data.price.product'],
        ]);
        $items = iterator_to_array($page->autoPagingIterator(), false);

        return Collection::constructFrom(['object' => 'list', 'data' => array_map(fn ($item) => $item->toArray(), $items), 'has_more' => false]);
    }

    public function paymentIntent(string $id): array
    {
        return Cashier::stripe()->paymentIntents->retrieve($id, ['expand' => ['latest_charge']])->toArray();
    }

    public function refund(array $payload, string $key): array
    {
        return Cashier::stripe()->refunds->create($payload, ['idempotency_key' => $key])->toArray();
    }

    public function capture(string $id, int $amount, string $key): array
    {
        return Cashier::stripe()->paymentIntents->capture($id, ['amount_to_capture' => $amount], ['idempotency_key' => $key])->toArray();
    }

    public function cancel(string $id, string $key): array
    {
        return Cashier::stripe()->paymentIntents->cancel($id, [], ['idempotency_key' => $key])->toArray();
    }
}
