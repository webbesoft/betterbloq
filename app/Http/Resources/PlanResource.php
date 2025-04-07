<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'stripe_plan' => $this->stripe_plan,
            'price' => $this->price,
            'description' => $this->description,
            'features' => $this->planFeatures,
            'limits' => $this->planLimits,
        ];
    }
}
