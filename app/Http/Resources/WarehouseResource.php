<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $supportedConditions = [];
        if ($this->supported_storage_conditions) {
            $decodedConditions = json_decode($this->supported_storage_conditions, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $supportedConditions = $decodedConditions;
            } else {
                $supportedConditions = $this->supported_storage_conditions;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'total_capacity' => (float) $this->total_capacity,
            'available_capacity' => (float) $this->available_capacity,
            'total_capacity_unit' => $this->total_capacity_unit,
            'default_storage_price_per_unit' => (float) $this->default_storage_price_per_unit,
            'default_storage_price_period' => $this->default_storage_price_period, // 'hours', 'days', 'weeks', 'months'
            'supported_storage_conditions' => $supportedConditions,
            'is_active' => (bool) $this->is_active,
            'user_id' => $this->user_id,
        ];
    }
}
