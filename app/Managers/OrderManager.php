<?php

namespace App\Managers;

use App\Models\Order;

class OrderManager
{
    public function __construct(public Order $order) {}

    public function onOrderCreated()
    {
        // TODO: whatever we currently do in the observer

        // TODO: send internal email
    }

    public function onOrderUpdated()
    {
        // TODO: update purchase pool and possibly storage order
    }

    public function onOrderDeleted() {}
}
