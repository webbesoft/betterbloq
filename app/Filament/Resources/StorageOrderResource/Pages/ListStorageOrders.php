<?php

namespace App\Filament\Resources\StorageOrderResource\Pages;

use App\Filament\Resources\StorageOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStorageOrders extends ListRecords
{
    protected static string $resource = StorageOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
