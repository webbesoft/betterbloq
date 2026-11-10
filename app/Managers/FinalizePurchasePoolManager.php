<?php

namespace App\Managers;

use App\Models\PurchaseCycle;
use App\Services\CycleFinalizationService;

class FinalizePurchasePoolManager
{
    public static function canFinalizeCycle(PurchaseCycle $purchaseCycle): bool
    {
        return in_array($purchaseCycle->status, ['active', 'calculating_discounts'], true);
    }

    public static function finalize(PurchaseCycle $purchaseCycle): array
    {
        return app(CycleFinalizationService::class)->finalize($purchaseCycle->id);
    }
}
