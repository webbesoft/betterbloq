<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use App\Models\User;
use App\Models\Vendor;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // This populates 'model_identifier' when loading an existing record for editing
        if (isset($data['model']) && isset($data['model_id'])) {
            $data['model_identifier'] = $data['model'].'_'.$data['model_id'];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['model_identifier']) && ! empty($data['model_identifier'])) {
            [$modelPrefix, $modelId] = explode('_', $data['model_identifier'], 2);

            $modelMap = [
                'User' => User::class,
                'Vendor' => Vendor::class,
                // 'Warehouse' => Warehouse::class,
            ];

            if (array_key_exists($modelPrefix, $modelMap)) {
                $data['model_type'] = $modelMap[$modelPrefix];
                $data['model_id'] = (int) $modelId;
            }
        }
        unset($data['model_identifier']); // Clean up the temporary field

        return $data;
    }
}
