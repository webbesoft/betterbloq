<?php

namespace App\Filament\Resources\PurchasePoolTemplateResource\Pages;

use App\Filament\Resources\PurchasePoolTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchasePoolTemplates extends ListRecords
{
    protected static string $resource = PurchasePoolTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
