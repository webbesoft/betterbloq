<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => env('AWS_URL') . '/' . $this->image,
            'price' => $this->price,
            'unit' => $this->unit,
            'vendor' => $this->vendor,
            'category' => $this->category->name,
            'preparation_time' => $this->preparation_time ?? null,
        ];
    }
}
