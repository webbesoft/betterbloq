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
        return [
            'id' => $this->id,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'email' => $this->email,
            'phone' => $this->phone,

            'address' => $this->address,

            //            'stripe_session_id' => $this->stripe_session_id, // Include if relevant
            //            'created_at' => $this->created_at?->toIso8601String(), // Format timestamp
            //            'updated_at' => $this->updated_at?->toIso8601String(), // Format timestamp

            'product' => new ProductResource($this->whenLoaded('lineItems.product')) ?? null,
            'purchase_pool' => new PurchasePoolResource($this->whenLoaded('lineItems.purchasePool')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
        ];
    }
}
