<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasePoolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'current_volume' => $this->current_volume,
            'target_volume' => $this->target_volume,
            'target_delivery_date' => $this->target_delivery_date,
            'min_orders_for_discount' => $this->min_orders_for_discount,
            'max_orders' => $this->max_orders,
            'start_date' => $this->purchaseCycle->start_date,
            'end_date' => $this->purchaseCycle->end_date,
            'status' => $this->cycle_status,
            'tiers' => $this->purchasePoolTiers,
        ];
    }
}
