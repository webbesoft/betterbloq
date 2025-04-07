<?php

namespace App\Filament\Resources\PurchasePoolTierResource\Pages;

use App\Filament\Resources\PurchasePoolTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchasePoolTiers extends ListRecords
{
    protected static string $resource = PurchasePoolTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
