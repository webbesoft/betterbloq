<?php

namespace App\Filament\Resources\OrderLineItemResource\Pages;

use App\Filament\Resources\OrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderLineItems extends ListRecords
{
    protected static string $resource = OrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
