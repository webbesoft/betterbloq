<?php

namespace App\Filament\Resources\PurchasePoolTierResource\Pages;

use App\Filament\Resources\PurchasePoolTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePoolTier extends EditRecord
{
    protected static string $resource = PurchasePoolTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
