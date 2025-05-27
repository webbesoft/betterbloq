<?php

namespace App\Filament\Resources\OrderLineItemResource\Pages;

use App\Filament\Resources\OrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrderLineItem extends ViewRecord
{
    protected static string $resource = OrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
