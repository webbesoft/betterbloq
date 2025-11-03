<?php

namespace App\Filament\Resources\StorageOrderLineItemResource\Pages;

use App\Filament\Resources\StorageOrderLineItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStorageOrderLineItems extends ListRecords
{
    protected static string $resource = StorageOrderLineItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
