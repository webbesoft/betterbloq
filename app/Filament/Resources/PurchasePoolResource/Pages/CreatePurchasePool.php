<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use App\Managers\PurchasePoolManager;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchasePool extends CreateRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $purchasePoolManager = new PurchasePoolManager($this->record);

        return $purchasePoolManager->onSaving(
            $data
        );
    }
}
