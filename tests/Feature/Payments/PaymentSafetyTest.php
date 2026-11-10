<?php

use App\Listeners\StripeEventListener;
use App\Mail\OrderFinalisedNotification;
use App\Models\CheckoutAttempt;
use App\Models\CycleProductVolume;
use App\Models\Order;
use App\Models\PaymentOperation;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use App\Models\StorageOrder;
use App\Models\User;
use App\Payments\StripeGateway;
use App\Services\CheckoutReconciliationService;
use App\Services\CycleFinalizationService;
use App\Services\OrderService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;
use Stripe\Collection;

beforeEach(function () {
    Mail::fake();
    $this->user = User::factory()->create(['stripe_id' => 'cus_test']);
    $this->cycle = PurchaseCycle::create(['name' => 'Test cycle', 'start_date' => today()->subDay(), 'end_date' => today()->addDay(), 'status' => 'active']);
    $vendor = DB::table('vendors')->insertGetId(['name' => 'Vendor', 'phone' => '123', 'user_id' => $this->user->id]);
    $category = DB::table('categories')->insertGetId(['name' => 'Materials']);
    $id = DB::table('products')->insertGetId([
        'name' => 'Bricks', 'description' => 'Bricks', 'price' => 100,
        'vendor_id' => $vendor, 'category_id' => $category,
        'storage_conditions_required' => '[]', 'stripe_price_id' => 'price_test',
    ]);
    $this->product = Product::findOrFail($id);
    $this->pool = PurchasePool::create([
        'product_id' => $id, 'vendor_id' => $vendor, 'purchase_cycle_id' => $this->cycle->id,
        'target_delivery_date' => today()->addDays(3), 'cycle_status' => 'accumulating', 'current_volume' => 0, 'target_volume' => 100,
    ]);
    $this->cart = ['items' => [[
        'product_id' => $id, 'purchase_cycle_id' => $this->cycle->id, 'quantity' => 2,
        'expected_delivery_date' => today()->addDays(3)->toDateString(),
        'final_line_price' => '0', 'product_subtotal' => '0', 'storage_cost_applied' => '0',
    ]]];
});

function paymentTestLines(CheckoutAttempt $attempt): Collection
{
    return Collection::constructFrom(['object' => 'list', 'data' => array_map(fn ($line) => [
        'id' => 'li_'.$line['id'], 'description' => $line['name'], 'currency' => 'usd',
        'quantity' => $line['quantity'], 'amount_total' => $line['unit_amount'] * $line['quantity'],
        'price' => ['id' => 'price_'.$line['id'], 'unit_amount' => $line['unit_amount'],
            'product' => ['id' => 'prod_'.$line['id'], 'metadata' => ['checkout_line_id' => $line['id']]]],
    ], $attempt->snapshot)]);
}

function paymentTestSession(CheckoutAttempt $attempt): array
{
    return [
        'id' => 'cs_test', 'mode' => 'payment', 'customer' => 'cus_test',
        'payment_status' => 'paid', 'payment_intent' => 'pi_test', 'currency' => 'usd',
        'amount_total' => array_sum(array_map(fn ($line) => $line['unit_amount'] * $line['quantity'], $attempt->snapshot)),
        'metadata' => ['checkout_attempt_id' => $attempt->id, 'user_id' => (string) $attempt->user_id],
    ];
}

function startPaymentTestCheckout(object $test): CheckoutAttempt
{
    $mock = Mockery::mock(StripeGateway::class);
    $mock->shouldReceive('createSession')->once()->andReturn(['id' => 'cs_test', 'url' => 'https://checkout.stripe.com/test']);
    app()->instance(StripeGateway::class, $mock);
    OrderService::createOrder($test->user, $test->cart);

    return CheckoutAttempt::firstOrFail();
}

it('prices on the server and reuses the same checkout for duplicate submissions', function () {
    $attempt = startPaymentTestCheckout($this);
    OrderService::createOrder($this->user, $this->cart);
    expect(CheckoutAttempt::count())->toBe(1)
        ->and($attempt->snapshot[0]['unit_amount'])->toBe(10000)
        ->and($attempt->stripe_payload['line_items'][0]['price_data']['unit_amount'])->toBe(10000);
});

it('creates an order atomically and applies repeated deliveries only once', function () {
    $attempt = startPaymentTestCheckout($this);
    $session = paymentTestSession($attempt);
    $this->mock(StripeGateway::class)->shouldReceive('lineItems')->once()->andReturn(paymentTestLines($attempt));
    $event = new WebhookReceived(['type' => 'checkout.session.completed', 'data' => ['object' => $session]]);
    $listener = app(StripeEventListener::class);
    $listener->handle($event);
    $listener->handle($event);
    expect(Order::count())->toBe(1)->and($this->pool->fresh()->current_volume)->toBe(2.0)
        ->and(Order::first()->purchase_cycle_id)->toBe($this->cycle->id)
        ->and($attempt->fresh()->status)->toBe('complete');
});

it('propagates webhook failures and leaves the order and volumes untouched', function () {
    $attempt = startPaymentTestCheckout($this);
    $this->mock(StripeGateway::class)->shouldReceive('lineItems')->andThrow(new RuntimeException('Provider unavailable'));
    expect(fn () => app(StripeEventListener::class)->handle(new WebhookReceived([
        'type' => 'checkout.session.completed', 'data' => ['object' => paymentTestSession($attempt)],
    ])))->toThrow(RuntimeException::class, 'Provider unavailable');
    expect(Order::count())->toBe(0)->and($this->pool->fresh()->current_volume)->toBe(0.0);
});

it('lets subscription and invoice events reach Cashier', function () {
    app(StripeEventListener::class)->handle(new WebhookReceived(['type' => 'customer.subscription.created']));
    app(StripeEventListener::class)->handle(new WebhookReceived(['type' => 'invoice.payment_succeeded']));
    expect(Order::count())->toBe(0);
});

it('fulfills asynchronous success without provisioning on unpaid completion', function () {
    $attempt = startPaymentTestCheckout($this);
    $session = paymentTestSession($attempt);
    $unpaid = array_merge($session, ['payment_status' => 'unpaid']);
    $listener = app(StripeEventListener::class);
    $listener->handle(new WebhookReceived(['type' => 'checkout.session.completed', 'data' => ['object' => $unpaid]]));
    expect(Order::count())->toBe(0)->and($attempt->fresh()->status)->toBe('processing');
    $this->mock(StripeGateway::class)->shouldReceive('lineItems')->once()->andReturn(paymentTestLines($attempt));
    $listener->handle(new WebhookReceived(['type' => 'checkout.session.async_payment_succeeded', 'data' => ['object' => $session]]));
    expect(Order::count())->toBe(1);
});

it('rejects incomplete line sets without committing partial orders', function () {
    $attempt = startPaymentTestCheckout($this);
    $session = paymentTestSession($attempt);
    $session['amount_total']++;
    expect(fn () => app(OrderService::class)->createOrderFromStripeSession(
        $session, $this->user, $session['metadata'], paymentTestLines($attempt), collect($attempt->snapshot)->keyBy('id')->all(),
    ))->toThrow(RuntimeException::class, 'amount mismatch');
    expect(Order::count())->toBe(0)->and($this->pool->fresh()->current_volume)->toBe(0.0);
});

it('denies access to another customers order', function () {
    $attempt = startPaymentTestCheckout($this);
    $session = paymentTestSession($attempt);
    $order = app(OrderService::class)->createOrderFromStripeSession($session, $this->user, $session['metadata'], paymentTestLines($attempt), collect($attempt->snapshot)->keyBy('id')->all());
    $other = User::factory()->create();
    $this->actingAs($other)->get(route('orders.show', $order))->assertForbidden();
});

it('denies reading updating and deleting another customers project', function () {
    $id = DB::table('projects')->insertGetId([
        'name' => 'Private project', 'budget' => 100, 'start_date' => today(),
        'target_completion_date' => today()->addMonth(), 'user_id' => $this->user->id,
    ]);
    $other = User::factory()->create();
    $this->actingAs($other)->get(route('projects.edit', $id))->assertForbidden();
    $this->put(route('projects.update', $id), [])->assertForbidden();
    $this->delete(route('projects.destroy', $id))->assertForbidden();
    expect(Project::find($id))->not->toBeNull();
});

function createPaidTestOrder(object $test): Order
{
    $attempt = startPaymentTestCheckout($test);
    $session = paymentTestSession($attempt);

    return app(OrderService::class)->createOrderFromStripeSession($session, $test->user, $session['metadata'], paymentTestLines($attempt), collect($attempt->snapshot)->keyBy('id')->all());
}

function addTestDiscount(PurchasePool $pool, float $percentage): void
{
    DB::table('purchase_pool_tiers')->insert([
        'name' => 'Volume tier', 'description' => 'Volume discount', 'min_volume' => 0,
        'max_volume' => null, 'discount_percentage' => $percentage, 'purchase_pool_id' => $pool->id,
    ]);
}

it('refunds the final discount once from the original line price', function () {
    addTestDiscount($this->pool, 10);
    $order = createPaidTestOrder($this);
    $this->mock(StripeGateway::class, function ($mock) {
        $mock->shouldReceive('paymentIntent')->once()->with('pi_test')->andReturn([
            'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 20000,
            'latest_charge' => ['amount_refunded' => 0],
        ]);
        $mock->shouldReceive('refund')->once()->withArgs(fn ($payload, $key) => $payload['amount'] === 2000 && str_starts_with($key, 'finalize_'))
            ->andReturn(['id' => 're_test', 'status' => 'succeeded']);
    });
    $result = app(CycleFinalizationService::class)->finalize($this->cycle->id);
    expect($result)->toBe(['successful' => 1, 'failed' => 0])
        ->and((float) $order->fresh()->final_amount)->toBe(180.0)
        ->and($this->cycle->fresh()->status)->toBe('closed')
        ->and(DB::table('payment_notifications')->count())->toBe(1);
    expect(fn () => app(CycleFinalizationService::class)->finalize($this->cycle->id))->toThrow(RuntimeException::class, 'already been finalized');
});

it('replays the identical refund after an uncertain provider response', function () {
    addTestDiscount($this->pool, 10);
    $order = createPaidTestOrder($this);
    $requests = [];
    $this->mock(StripeGateway::class, function ($mock) use (&$requests) {
        $mock->shouldReceive('paymentIntent')->once()->andReturn([
            'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 20000, 'latest_charge' => ['amount_refunded' => 0],
        ]);
        $mock->shouldReceive('refund')->twice()->andReturnUsing(function ($request, $key) use (&$requests) {
            $requests[] = [$request, $key];
            if (count($requests) === 1) {
                throw new RuntimeException('Connection lost after Stripe processed the refund');
            }

            return ['id' => 're_recovered', 'status' => 'succeeded'];
        });
    });
    $service = app(CycleFinalizationService::class);
    expect($service->finalize($this->cycle->id)['failed'])->toBe(1)
        ->and($order->fresh()->status)->toBe('refund_failed');
    // Subsequent pricing changes must not alter the persisted operation.
    DB::table('purchase_pool_tiers')->update(['discount_percentage' => 30]);
    expect($service->finalize($this->cycle->id)['successful'])->toBe(1)
        ->and($requests[0])->toBe($requests[1])
        ->and((float) $order->fresh()->final_amount)->toBe(180.0);
});

it('blocks finalization while a checkout can still become payable', function () {
    startPaymentTestCheckout($this);
    expect(fn () => app(CycleFinalizationService::class)->finalize($this->cycle->id))
        ->toThrow(RuntimeException::class, 'outstanding checkouts');
    expect($this->cycle->fresh()->status)->toBe('active');
});

it('rejects concurrent finalization and recovers an expired lease', function () {
    $this->cycle->forceFill(['status' => 'processing_payments', 'finalization_token' => 'running', 'finalization_lease_until' => now()->addMinute()])->save();
    $service = app(CycleFinalizationService::class);
    expect(fn () => $service->finalize($this->cycle->id))->toThrow(RuntimeException::class, 'already being finalized');
    $this->cycle->forceFill(['finalization_lease_until' => now()->subMinute()])->save();
    expect($service->finalize($this->cycle->id))->toBe(['successful' => 0, 'failed' => 0]);
});

it('keeps completed payments completed when notification delivery fails', function () {
    $order = createPaidTestOrder($this);
    $this->mock(StripeGateway::class)->shouldReceive('paymentIntent')->once()->andReturn([
        'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 20000, 'latest_charge' => ['amount_refunded' => 0],
    ]);
    app(CycleFinalizationService::class)->finalize($this->cycle->id);
    Mail::swap(Mockery::mock(Mailer::class, function ($mock) {
        $mock->shouldReceive('to')->andThrow(new RuntimeException('Mail server offline'));
    }));
    $this->artisan('payments:send-notifications')->assertFailed();
    expect($order->fresh()->status)->toBe('completed')
        ->and(DB::table('payment_notifications')->first()->sent_at)->toBeNull();
});

it('reconciles a lost session creation response without creating a second session', function () {
    $attempt = startPaymentTestCheckout($this);
    $attempt->update(['status' => 'creating', 'stripe_session_id' => null, 'url' => null]);
    $session = array_merge(paymentTestSession($attempt), ['status' => 'complete']);
    $this->mock(StripeGateway::class, function ($mock) use ($session, $attempt) {
        $mock->shouldReceive('findSession')->once()->andReturn($session);
        $mock->shouldReceive('lineItems')->once()->andReturn(paymentTestLines($attempt));
    });
    app(CheckoutReconciliationService::class)->reconcile($attempt);
    expect(Order::count())->toBe(1)->and($attempt->fresh()->status)->toBe('complete');
});

it('calculates storage from the configured warehouse and links it after payment', function () {
    $warehouseId = DB::table('warehouses')->insertGetId([
        'name' => 'Warehouse', 'user_id' => $this->user->id, 'is_active' => true,
        'total_capacity' => 1000, 'available_capacity' => 1000, 'total_capacity_unit' => 'sq ft',
        'default_storage_price_per_unit' => 7, 'default_storage_price_period' => 'weeks',
        'supported_storage_conditions' => '[]',
    ]);
    DB::table('products')->where('id', $this->product->id)->update([
        'preferred_warehouse_id' => $warehouseId, 'default_length' => 1, 'default_width' => 1,
        'default_height' => 1, 'storage_unit_of_measure' => 'ft', 'storable' => true,
    ]);
    $this->cart['items'][0]['expected_delivery_date'] = today()->addDays(12)->toDateString();
    $this->cart['items'][0]['requires_storage_acknowledged'] = true;
    $attempt = startPaymentTestCheckout($this);
    expect($attempt->snapshot[1]['unit_amount'])->toBe(400)
        ->and(StorageOrder::count())->toBe(0);
    $session = paymentTestSession($attempt);
    app(OrderService::class)->createOrderFromStripeSession($session, $this->user, $session['metadata'], paymentTestLines($attempt), collect($attempt->snapshot)->keyBy('id')->all());
    expect(StorageOrder::first()->order_id)->toBe(Order::first()->id)
        ->and((float) StorageOrder::first()->preliminary_storage_cost_estimate)->toBe(4.0);
});

it('finalizes an order once using each products own tier and preserves storage charges', function () {
    addTestDiscount($this->pool, 10);
    $order = createPaidTestOrder($this);
    $secondId = DB::table('products')->insertGetId([
        'name' => 'Cement', 'description' => 'Cement', 'price' => 50,
        'vendor_id' => $this->product->vendor_id, 'category_id' => $this->product->category_id,
        'storage_conditions_required' => '[]',
    ]);
    $secondPool = PurchasePool::create([
        'product_id' => $secondId, 'vendor_id' => $this->product->vendor_id, 'purchase_cycle_id' => $this->cycle->id,
        'target_delivery_date' => today()->addDays(3), 'cycle_status' => 'accumulating', 'current_volume' => 2, 'target_volume' => 100,
    ]);
    addTestDiscount($secondPool, 20);
    CycleProductVolume::create(['purchase_cycle_id' => $this->cycle->id, 'product_id' => $secondId, 'total_aggregated_quantity' => 2]);
    $order->lineItems()->create([
        'product_id' => $secondId, 'purchase_pool_id' => $secondPool->id, 'quantity' => 2,
        'price_per_unit' => 50, 'total_price' => 100, 'original_unit_amount' => 5000, 'charged_amount' => 10000,
    ]);
    $order->lineItems()->create([
        'product_id' => null, 'purchase_pool_id' => $this->pool->id, 'quantity' => 1, 'description' => 'Storage Fee',
        'price_per_unit' => 4, 'total_price' => 4, 'charged_amount' => 400,
    ]);
    $order->update(['initial_amount' => 304, 'total_amount' => 304]);
    $this->mock(StripeGateway::class, function ($mock) {
        $mock->shouldReceive('paymentIntent')->once()->andReturn([
            'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 30400, 'latest_charge' => ['amount_refunded' => 0],
        ]);
        $mock->shouldReceive('refund')->once()->withArgs(fn ($payload) => $payload['amount'] === 4000)
            ->andReturn(['id' => 're_multi', 'status' => 'succeeded']);
    });
    expect(app(CycleFinalizationService::class)->finalize($this->cycle->id)['successful'])->toBe(1)
        ->and((float) $order->fresh()->final_amount)->toBe(264.0)
        ->and(PaymentOperation::count())->toBe(1);
});

it('finds an old refund instead of reissuing it after idempotency key expiry', function () {
    addTestDiscount($this->pool, 10);
    $order = createPaidTestOrder($this);
    $this->mock(StripeGateway::class, function ($mock) {
        $mock->shouldReceive('paymentIntent')->once()->andReturn([
            'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 20000, 'latest_charge' => ['amount_refunded' => 0],
        ]);
        $mock->shouldReceive('refund')->once()->andThrow(new RuntimeException('Lost response'));
    });
    $service = app(CycleFinalizationService::class);
    $service->finalize($this->cycle->id);
    DB::table('payment_operations')->update(['created_at' => now()->subDays(2)]);
    $this->mock(StripeGateway::class, function ($mock) {
        $mock->shouldNotReceive('refund');
        $mock->shouldReceive('findRefund')->once()->andReturn(['id' => 're_original', 'status' => 'succeeded']);
    });
    expect($service->finalize($this->cycle->id)['successful'])->toBe(1)
        ->and((float) $order->fresh()->final_amount)->toBe(180.0);
});

it('polls a pending refund before marking the order complete', function () {
    addTestDiscount($this->pool, 10);
    $order = createPaidTestOrder($this);
    $this->mock(StripeGateway::class, function ($mock) {
        $mock->shouldReceive('paymentIntent')->once()->andReturn([
            'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 20000, 'latest_charge' => ['amount_refunded' => 0],
        ]);
        $mock->shouldReceive('refund')->once()->andReturn(['id' => 're_pending', 'status' => 'pending']);
        $mock->shouldReceive('refundById')->once()->with('re_pending')->andReturn(['id' => 're_pending', 'status' => 'succeeded']);
    });
    $service = app(CycleFinalizationService::class);
    expect($service->finalize($this->cycle->id)['failed'])->toBe(1)
        ->and($order->fresh()->status)->toBe('refund_failed');
    expect($service->finalize($this->cycle->id)['successful'])->toBe(1)
        ->and($order->fresh()->status)->toBe('completed');
});

it('rejects unsigned webhooks when signature verification is unconfigured', function () {
    config(['cashier.webhook.secret' => null]);
    $this->postJson('/stripe/webhook', ['type' => 'payment_intent.succeeded'])->assertStatus(503);
});

it('checks webhook signatures before dispatching payment events', function () {
    config(['cashier.webhook.secret' => 'whsec_test']);
    $this->postJson('/stripe/webhook', ['type' => 'payment_intent.succeeded'])->assertForbidden();
    $payload = json_encode(['id' => 'evt_test', 'type' => 'payment_intent.succeeded']);
    $time = time();
    $signature = hash_hmac('sha256', $time.'.'.$payload, 'whsec_test');
    $this->call('POST', '/stripe/webhook', [], [], [], [
        'CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => "t={$time},v1={$signature}",
    ], $payload)->assertOk();
});

it('delivers the committed notification outbox independently and only once normally', function () {
    $order = createPaidTestOrder($this);
    $this->mock(StripeGateway::class)->shouldReceive('paymentIntent')->once()->andReturn([
        'status' => 'succeeded', 'currency' => 'usd', 'amount_received' => 20000, 'latest_charge' => ['amount_refunded' => 0],
    ]);
    app(CycleFinalizationService::class)->finalize($this->cycle->id);
    $this->artisan('payments:send-notifications')->assertSuccessful();
    $this->artisan('payments:send-notifications')->assertSuccessful();
    Mail::assertSent(OrderFinalisedNotification::class, 1);
    expect(DB::table('payment_notifications')->first()->sent_at)->not->toBeNull();
    expect((new OrderFinalisedNotification($order->fresh()))->render())->toContain('Initial payment');
});

it('matches checkout lines by identity even when Stripe returns a different order', function () {
    $attempt = startPaymentTestCheckout($this);
    $snapshot = $attempt->snapshot;
    $storage = array_merge($snapshot[0], ['id' => 'storage_line', 'type' => 'storage', 'name' => 'Storage Fee', 'quantity' => 1, 'unit_amount' => 400]);
    $snapshot[] = $storage;
    $attempt->update(['snapshot' => $snapshot]);
    $session = paymentTestSession($attempt);
    $lines = paymentTestLines($attempt);
    $lines->data = array_reverse($lines->data);
    $order = app(OrderService::class)->createOrderFromStripeSession($session, $this->user, $session['metadata'], $lines, collect($snapshot)->keyBy('id')->all());
    expect($order->lineItems()->whereNull('product_id')->first()->description)->toBe('Storage Fee')
        ->and($order->lineItems()->whereNotNull('product_id')->first()->product_id)->toBe($this->product->id)
        ->and($this->pool->fresh()->current_volume)->toBe(2.0);
});

it('enforces unique cycle product aggregates at the database boundary', function () {
    CycleProductVolume::create(['purchase_cycle_id' => $this->cycle->id, 'product_id' => $this->product->id, 'total_aggregated_quantity' => 1]);
    expect(fn () => DB::transaction(fn () => CycleProductVolume::create([
        'purchase_cycle_id' => $this->cycle->id, 'product_id' => $this->product->id, 'total_aggregated_quantity' => 1,
    ])))->toThrow(QueryException::class);
    expect(CycleProductVolume::count())->toBe(1);
});

it('assigns project ownership from the session and ignores submitted owner IDs', function () {
    $other = User::factory()->create();
    $this->actingAs($this->user)->post(route('projects.store'), [
        'name' => 'My project', 'budget' => 100, 'start_date' => today()->toDateString(),
        'target_completion_date' => today()->addMonth()->toDateString(), 'user_id' => $other->id,
    ])->assertRedirect(route('projects.index'));
    expect(Project::first()->user_id)->toBe($this->user->id);
});

it('records verified historical prices without issuing a refund', function () {
    $order = createPaidTestOrder($this);
    $line = $order->lineItems()->first();
    $line->update(['original_unit_amount' => null, 'charged_amount' => null]);
    $this->mock(StripeGateway::class, function ($mock) {
        $mock->shouldReceive('paymentIntent')->once()->andReturn([
            'currency' => 'usd', 'status' => 'succeeded', 'amount_received' => 20000, 'latest_charge' => ['amount_refunded' => 0],
        ]);
        $mock->shouldNotReceive('refund');
    });
    $this->artisan('payments:reconcile-order-prices', ['order' => $order->id, 'original-unit-amounts' => json_encode([$line->id => 12500])])->assertSuccessful();
    expect($line->fresh()->original_unit_amount)->toBe(12500)
        ->and($line->fresh()->charged_amount)->toBe(20000)
        ->and(PaymentOperation::count())->toBe(0);
});

it('renders storage and final refund amounts on the invoice', function () {
    $order = createPaidTestOrder($this);
    $order->lineItems()->create([
        'product_id' => null, 'purchase_pool_id' => $this->pool->id, 'quantity' => 1, 'description' => 'Storage Fee',
        'price_per_unit' => 4, 'total_price' => 4, 'charged_amount' => 400,
    ]);
    $order->update(['initial_amount' => 204, 'total_amount' => 204, 'final_amount' => 184, 'status' => 'completed']);
    $html = view('invoices.order', ['order' => $order->fresh()->load('lineItems.product')])->render();
    expect($html)->toContain('Storage Fee')->toContain('Discount refunded:')->toContain('-$20.00')->toContain('$184.00');
});
