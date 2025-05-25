<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $cacheKey = 'user_orders_'.$userId.'_page_'.$request->query('page', 1);
        $cacheDuration = 600;

        $orders = Cache::remember($cacheKey, $cacheDuration, function () use ($request) {
            $ordersQuery = Order::query();

            return $ordersQuery
                ->where('user_id', $request->user()->id)
                ->with(['lineItems', 'vendor', 'lineItems.product.category', 'lineItems.purchasePool.purchasePoolTiers'])
                ->paginate(10);
        });

        return Inertia::render('shop/orders/index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $order->load(['lineItems', 'lineItems.product', 'vendor', 'lineItems.purchasePool', 'lineItems.purchasePool.purchasePoolTiers']);

        return Inertia::render('shop/orders/show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse|Response
    {
        $user = $request->user();

        if (! $user) {
            Session::flash('message', ['error' => 'You must be logged in to place an order.']);

            return redirect()->route('login');
        }

        $validated = $request->validated();

        $serviceData = [];

        // #! Order from cart has multiple items
        if (isset($validated['items']) && is_array($validated['items'])) {
            $serviceData['items'] = $validated['items'];
            foreach ($serviceData['items'] as $cartItem) {
                if (empty($cartItem['product_id']) || empty($cartItem['quantity'])) {
                    Session::flash('message', ['error' => 'Invalid cart item data. Each item must have a product ID and quantity.']);

                    return back()->withInput()->with('error', 'Invalid cart item data.');
                }
            }
            $serviceData['expected_delivery_date'] = $validated['expected_delivery_date'] ?? null;
        }
        // #! scenario 2: single product order
        elseif (isset($validated['product_id'])) {
            $serviceData['items'] = [
                [
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'purchase_cycle_id' => $validated['purchase_cycle_id'] ?? null,
                ],
            ];
            $serviceData['expected_delivery_date'] = $validated['expected_delivery_date'] ?? null;
        } else {
            Session::flash('message', ['error' => 'Invalid order data submitted.']);

            return back()->withInput()->with('error', 'Invalid order data submitted.');
        }

        $orderResponse = OrderService::createOrder($user, $serviceData);

        if (! data_get($orderResponse, 'error') && ! is_null($orderResponse)) {
            return Inertia::render('shop/payment-pending', [
                'url' => data_get($orderResponse, 'url'),
            ]);
        }

        Session::flash('message', ['error' => data_get($orderResponse, 'error')]);

        return back()->with('error', data_get($orderResponse, 'error'));
    }
}
