<?php

namespace App\Filament\Resources\PurchaseCycleResource\Pages;

use App\Filament\Resources\PurchaseCycleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseCycles extends ListRecords
{
    protected static string $resource = PurchaseCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
