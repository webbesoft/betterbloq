<?php

namespace App\Filament\Resources\StorageOrderResource\Pages;

use App\Filament\Resources\StorageOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStorageOrder extends ViewRecord
{
    protected static string $resource = StorageOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
