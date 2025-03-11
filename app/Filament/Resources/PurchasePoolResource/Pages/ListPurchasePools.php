<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchasePools extends ListRecords
{
    protected static string $resource = PurchasePoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
