<?php

namespace App\Apps\BulkBuy\Controllers;

use App\Services\OrderService;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $order = OrderService::createOrder($request->user(), $validated);

        if (! data_get($order, 'error')) {
            return to_route('orders.pay', $order);
        }

        Session::flash('message', data_get($order, 'error'));

        return back();
    }
}
