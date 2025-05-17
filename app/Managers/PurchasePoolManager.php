<?php

namespace App\Managers;

use App\Models\PurchasePool;

class PurchasePoolManager
{
    public function __construct(public PurchasePool $purchasePool) {}

    public function onPurchasePoolCreated() {}

    public function onPurchasePoolUpdated() {}

    public function onPurchasePoolDeleted() {}
}
