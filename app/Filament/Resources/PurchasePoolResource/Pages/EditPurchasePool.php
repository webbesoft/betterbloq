<?php

namespace App\Filament\Resources\PurchasePoolResource\Pages;

use App\Filament\Resources\PurchasePoolResource;
use App\Managers\PurchasePoolManager;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchasePool extends EditRecord
{
    protected static string $resource = PurchasePoolResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $purchasePoolManager = new PurchasePoolManager($this->record);

        return $purchasePoolManager->onSaving(
            $data
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
