<?php

use App\Listeners\StripeEventListener;
use App\Models\CheckoutAttempt;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseCycle;
use App\Models\PurchasePool;
use App\Models\User;
use App\Payments\StripeGateway;
use App\Services\OrderService;
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
