<?php

namespace App\Filament\Resources\StorageOrderLineItemResource\Pages;

use App\Filament\Resources\StorageOrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStorageOrderLineItem extends ViewRecord
{
    protected static string $resource = StorageOrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
