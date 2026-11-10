<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use App\Models\CycleProductVolume;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\Product;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use App\Models\StorageOrder;
use App\Models\User;
use App\Payments\StripeGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Stripe\Collection;

class OrderService
{
    public static function createOrder(User $user, array $data)
    {
        return app(self::class)->startCheckout($user, $data['items'] ?? []);
    }

    public function startCheckout(User $user, array $items): array
    {
        if (! $items) {
            throw ValidationException::withMessages(['items' => 'No items provided in the order.']);
        }
        // Ignore browser-calculated amounts when identifying or pricing the purchase.
        $intent = array_map(fn ($item) => [
            'product_id' => (int) $item['product_id'], 'quantity' => (int) $item['quantity'],
            'purchase_cycle_id' => (int) $item['purchase_cycle_id'],
            'expected_delivery_date' => $item['expected_delivery_date'],
            'requires_storage_acknowledged' => (bool) ($item['requires_storage_acknowledged'] ?? false),
        ], $items);
        usort($intent, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);
        if (count(array_unique(array_column($intent, 'product_id'))) !== count($intent)
            || count(array_unique(array_column($intent, 'purchase_cycle_id'))) !== 1) {
            throw ValidationException::withMessages(['items' => 'Use distinct products from one purchase cycle per checkout.']);
        }
        $fingerprint = hash('sha256', json_encode($intent, JSON_THROW_ON_ERROR));
        $gateway = app(StripeGateway::class);
        // Serialize customer creation as well as attempt reservation across submissions.
        $customerId = $user->stripe_id;
        if (! $customerId) {
            // Stripe customer creation itself uses a stable, per-user idempotency key.
            $customerId = $gateway->customer($user);
        }
        $attempt = DB::transaction(function () use ($user, $intent, $fingerprint, $customerId) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $existing = CheckoutAttempt::where('user_id', $user->id)->where('fingerprint', $fingerprint)
                ->whereNotIn('status', ['expired', 'failed'])->latest()->first();
            if ($existing) {
                return $existing;
            }
            $cycle = PurchaseCycle::whereKey($intent[0]['purchase_cycle_id'])->lockForUpdate()->firstOrFail();
            if ($cycle->status !== PurchaseCycle::STATUS_ACTIVE || $cycle->start_date > today()->toDateString()
                || $cycle->end_date < today()->toDateString()) {
                throw ValidationException::withMessages(['items' => 'The purchase cycle is not accepting orders.']);
            }
            $snapshot = $this->quote($intent);
            $id = (string) Str::uuid();
            $expiresAt = now()->addHour();
            $payload = [
                'mode' => 'payment', 'customer' => $customerId,
                'success_url' => route('orders.index').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cart.show'), 'expires_at' => $expiresAt->timestamp,
                'metadata' => ['checkout_attempt_id' => $id, 'user_id' => (string) $user->id],
                'payment_intent_data' => ['metadata' => ['checkout_attempt_id' => $id]],
                'line_items' => array_map(fn ($line) => [
                    'price_data' => [
                        'currency' => 'usd', 'unit_amount' => $line['unit_amount'],
                        'product_data' => ['name' => $line['name'], 'metadata' => ['checkout_line_id' => $line['id']]],
                    ],
                    'quantity' => $line['quantity'],
                ], $snapshot),
            ];

            return CheckoutAttempt::create([
                'id' => $id, 'user_id' => $user->id, 'purchase_cycle_id' => $cycle->id,
                'fingerprint' => $fingerprint, 'snapshot' => $snapshot, 'stripe_payload' => $payload,
                'expires_at' => $expiresAt,
            ]);
        }, 3);
        if ($attempt->status === 'complete') {
            return ['success' => true, 'url' => route('orders.index')];
        }
        if ($attempt->url && $attempt->expires_at->isFuture()) {
            return ['success' => true, 'url' => $attempt->url];
        }
        // Never silently start a second session when the first request's outcome is unknown.
        if ($attempt->expires_at->isPast()) {
            throw ValidationException::withMessages(['items' => 'This checkout is awaiting reconciliation. Please try again after its status is updated.']);
        }
        $session = $gateway->createSession($attempt->stripe_payload, 'checkout_'.$attempt->id);
        if (empty($session['url']) || empty($session['id'])) {
            throw new RuntimeException('Stripe returned an incomplete checkout session.');
        }
        CheckoutAttempt::whereKey($attempt->id)->update([
            'stripe_session_id' => $session['id'], 'url' => $session['url'],
        ]);
        CheckoutAttempt::whereKey($attempt->id)->where('status', 'creating')->update(['status' => 'open']);

        return ['success' => true, 'url' => $session['url']];
    }

    private function quote(array $items): array
    {
        $products = Product::whereIn('id', array_column($items, 'product_id'))->get()->keyBy('id');
        $snapshot = [];
        $vendors = [];
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $pool = PurchasePool::where('product_id', $item['product_id'])
                ->where('purchase_cycle_id', $item['purchase_cycle_id'])->where('cycle_status', 'accumulating')->first();
            if (! $product || ! $pool || $item['quantity'] < 1 || $product->price <= 0) {
                throw ValidationException::withMessages(['items' => 'A product or purchase pool is unavailable.']);
            }
            $vendors[] = $product->vendor_id;
            // Charge the original price once; unlock volume discounts through final refunds.
            $cents = (int) round((float) $product->price * 100);
            $line = [
                'id' => (string) Str::uuid(), 'type' => 'product', 'name' => $product->name,
                'product_id' => $product->id, 'purchase_pool_id' => $pool->id,
                'purchase_cycle_id' => $pool->purchase_cycle_id, 'vendor_id' => $product->vendor_id,
                'quantity' => $item['quantity'], 'unit_amount' => $cents, 'original_unit_amount' => $cents,
                'expected_delivery_date' => $item['expected_delivery_date'],
            ];
            $storage = app(StorageQuoteService::class)->quote($product, $pool, $item);
            $snapshot[] = $line;
            if ($storage) {
                $snapshot[] = array_merge($line, [
                    'id' => (string) Str::uuid(), 'type' => 'storage', 'name' => 'Storage Fee',
                    'quantity' => 1, 'unit_amount' => $storage['amount'], 'storage' => $storage,
                ]);
            }
        }
        if (count(array_unique($vendors)) !== 1) {
            throw ValidationException::withMessages(['items' => 'Use one vendor per checkout.']);
        }
        if (count($snapshot) > 100) {
            throw ValidationException::withMessages(['items' => 'The checkout contains too many line items.']);
        }

        return $snapshot;
    }

    public function createOrderFromStripeSession(array $session, User $user, array $metadata, Collection $lineItemsFromStripe, array $itemDetailsLookup): ?Order
    {
        return DB::transaction(function () use ($session, $user, $metadata, $lineItemsFromStripe, $itemDetailsLookup) {
            // Shared lock order with checkout reservation and cycle finalization.
            $attempt = isset($metadata['checkout_attempt_id'])
                ? CheckoutAttempt::whereKey($metadata['checkout_attempt_id'])->firstOrFail() : null;
            $cycleIds = array_unique(array_column($itemDetailsLookup, 'purchase_cycle_id'));
            if (count($cycleIds) !== 1 || empty($cycleIds[0])) {
                throw new RuntimeException('Checkout must identify exactly one purchase cycle.');
            }
            $cycle = PurchaseCycle::whereKey($cycleIds[0])->lockForUpdate()->firstOrFail();
            if ($attempt) {
                $attempt = CheckoutAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
                if ($attempt->user_id !== $user->id || ($attempt->stripe_session_id && $attempt->stripe_session_id !== $session['id'])) {
                    throw new RuntimeException('Checkout identity mismatch.');
                }
            }
            $existing = Order::withTrashed()->where('stripe_session_id', $session['id'])->first();
            if ($existing) {
                return $existing;
            }
            if (! in_array($session['payment_status'] ?? null, ['paid', 'no_payment_required'], true)) {
                throw new RuntimeException('Checkout payment is not confirmed.');
            }
            if (! in_array($cycle->status, ['active', 'calculating_discounts'], true)) {
                throw new RuntimeException('A payment arrived after cycle finalization started; reconciliation is required.');
            }
            $lines = $this->resolveLines($lineItemsFromStripe, $itemDetailsLookup);
            $total = array_sum(array_column($lines, 'charged_amount'));
            if (($session['currency'] ?? null) !== 'usd' || $total !== (int) $session['amount_total']) {
                throw new RuntimeException('Checkout currency or amount mismatch.');
            }
            $vendorIds = array_unique(array_column($lines, 'vendor_id'));
            if (count($vendorIds) !== 1) {
                throw new RuntimeException('Checkout must identify one vendor.');
            }
            $order = Order::create([
                'user_id' => $user->id,
                'purchase_cycle_id' => $cycle->id, 'vendor_id' => $vendorIds[0],
                'email' => data_get($session, 'customer_details.email') ?: $user->email,
                'phone' => data_get($session, 'customer_details.phone') ?: '',
                'address' => data_get($session, 'customer_details.address') ?? [],
                'status' => 'paid', 'stripe_session_id' => $session['id'],
                'payment_intent_id' => $session['payment_intent'] ?? null,
                'initial_amount' => $total / 100, 'total_amount' => $total / 100,
                'product_subtotal' => array_sum(array_map(fn ($line) => $line['type'] === 'product' ? $line['charged_amount'] : 0, $lines)) / 100,
                'storage_cost_applied' => array_sum(array_map(fn ($line) => $line['type'] === 'storage' ? $line['charged_amount'] : 0, $lines)) / 100,
            ]);
            foreach ($lines as $line) {
                OrderLineItem::create([
                    'order_id' => $order->id, 'product_id' => $line['type'] === 'product' ? $line['product_id'] : null,
                    'purchase_pool_id' => $line['purchase_pool_id'], 'description' => $line['type'] === 'storage' ? 'Storage Fee' : $line['name'],
                    'quantity' => $line['quantity'], 'price_per_unit' => $line['unit_amount'] / 100,
                    'total_price' => $line['charged_amount'] / 100, 'original_unit_amount' => $line['original_unit_amount'] ?? null,
                    'charged_amount' => $line['charged_amount'],
                ]);
                if ($line['type'] === 'product') {
                    $pool = PurchasePool::whereKey($line['purchase_pool_id'])->lockForUpdate()->firstOrFail();
                    $pool->increment('current_volume', $line['quantity']);
                    $volume = CycleProductVolume::firstOrCreate([
                        'purchase_cycle_id' => $cycle->id, 'product_id' => $line['product_id'],
                    ], ['total_aggregated_quantity' => 0]);
                    $volume->increment('total_aggregated_quantity', $line['quantity']);
                    $volume->update(['achieved_discount_percentage' => $pool->getApplicableDiscountPercentage()]);
                } elseif (isset($line['storage'])) {
                    StorageOrder::create([
                        'user_id' => $user->id, 'order_id' => $order->id,
                        'warehouse_id' => $line['storage']['warehouse_id'],
                        'requested_storage_start_date' => $line['storage']['start_date'],
                        'requested_storage_duration_estimate' => $line['storage']['days'].' days',
                        'preliminary_storage_cost_estimate' => $line['charged_amount'] / 100,
                        'status' => 'pending_arrival', 'notes' => 'Storage for product '.$line['product_id'],
                    ]);
                }
            }
            if ($attempt) {
                $attempt->update(['status' => 'complete', 'stripe_session_id' => $session['id']]);
            }

            return $order;
        }, 3);
    }

    private function resolveLines(Collection $stripeLines, array $details): array
    {
        $resolved = [];
        $used = [];
        foreach ($stripeLines->data as $stripeLine) {
            $key = data_get($stripeLine, 'price.product.metadata.checkout_line_id');
            $detail = $key ? ($details[$key] ?? null) : null;
            if (! $key) {
                // Legacy sessions have no persisted line identity. Match only an
                // unambiguous catalog price/name; never rely on Stripe list order.
                $matches = array_filter($details, function ($detail) use ($stripeLine) {
                    $product = Product::withTrashed()->find($detail['product_id']);

                    return $product && ($product->stripe_price_id === data_get($stripeLine, 'price.id')
                        || $product->name === $stripeLine->description);
                });
                if (count($matches) === 1) {
                    $detail = reset($matches);
                    $key = (string) $detail['product_id'];
                    $detail['type'] = 'product';
                    $detail['name'] = $detail['product_name'] ?? $stripeLine->description;
                    $detail['unit_amount'] = (int) $stripeLine->price->unit_amount;
                    // Legacy purchases do not have a reliable original-price snapshot.
                    // Finalization rejects these until explicitly reconciled.
                    $detail['original_unit_amount'] = null;
                }
            }
            if (! $detail || isset($used[$key]) || (int) $stripeLine->quantity !== (int) $detail['quantity']
                || (int) $stripeLine->price->unit_amount !== (int) $detail['unit_amount']
                || data_get($stripeLine, 'currency') !== 'usd') {
                throw new RuntimeException('Unmatched or inconsistent checkout line.');
            }
            $pool = PurchasePool::findOrFail($detail['purchase_pool_id']);
            if ($pool->product_id !== (int) $detail['product_id'] || $pool->purchase_cycle_id !== (int) $detail['purchase_cycle_id']) {
                throw new RuntimeException('Checkout pool identity mismatch.');
            }
            $detail['charged_amount'] = (int) $stripeLine->amount_total;
            if ($detail['charged_amount'] !== $detail['unit_amount'] * $detail['quantity']) {
                throw new RuntimeException('Unexpected checkout line adjustment.');
            }
            $used[$key] = true;
            $resolved[] = $detail;
        }
        if (count($used) !== count($details) || ! $resolved) {
            throw new RuntimeException('Checkout line set is incomplete.');
        }

        return $resolved;
    }
}
