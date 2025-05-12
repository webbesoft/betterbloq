<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use App\Models\User;
use App\Models\Vendor;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
