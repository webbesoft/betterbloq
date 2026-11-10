<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $productLine = $this->resource->relationLoaded('lineItems')
            ? $this->resource->lineItems->first(fn ($line) => $line->product_id !== null) : null;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,

            'address' => $this->address,

            'product' => new ProductResource($productLine?->product),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),

            'line_items' => OrderLineItemResource::collection($this->whenLoaded('lineItems')),

            'total_order_price' => $this->resource->orderTotal,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
