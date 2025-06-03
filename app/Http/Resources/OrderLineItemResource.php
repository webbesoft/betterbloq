<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'purchase_pool_id' => $this->purchase_pool_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'purchase_pool' => new PurchasePoolResource($this->whenLoaded('purchasePool')),
            'purchase_pool_tier' => $this->resource->purchasePool->getApplicableTier(),
            'price_per_unit' => $this->price_per_unit,
            'total_price' => $this->total_price,
            'description' => $this->description ?? null,
        ];
    }
}
