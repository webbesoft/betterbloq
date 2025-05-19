<?php

namespace App\Http\Resources;

use App\Http\Resources\OrderLineItemResource;
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
        return [
            'id' => $this->id,
            'status' => $this->status,
            'email' => $this->email,
            'phone' => $this->phone,

            'address' => $this->address,

            'product' => new ProductResource($this->whenLoaded('lineItems.product')) ?? null,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),

            'line_items' => OrderLineItemResource::collection($this->whenLoaded('lineItems')),

            'total_order_price' => $this->resource->orderTotal
        ];
    }
}
