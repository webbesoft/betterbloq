<?php

namespace App\Filament\Resources\OrderLineItemResource\Pages;

use App\Filament\Resources\OrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderLineItem extends EditRecord
{
    protected static string $resource = OrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
