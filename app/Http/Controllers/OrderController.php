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
                ->with(['product', 'purchasePool', 'vendor', 'product.category'])
                ->paginate(10);
        });

        return Inertia::render('shop/orders/index', [
            'orders' => OrderResource::collection($orders),
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $order->load(['product', 'purchasePool', 'vendor']);

        return Inertia::render('shop/orders/show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse|Response
    {
        $validated = $request->validated();

        $order = OrderService::createOrder($request->user(), $validated);

        if (! data_get($order, 'error')) {
            return Inertia::render('shop/payment-pending', [
                'url' => data_get($order, 'url'),
            ]);
        }

        Session::flash('message', ['error' => data_get($order, 'error')]);

        return back()->with('error', data_get($order, 'error'));
    }
}
