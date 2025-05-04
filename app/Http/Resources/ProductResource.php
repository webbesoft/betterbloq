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
            'image' => env('AWS_URL').'/'.$this->image,
            // 'additional_images' => $this->images->map(function ($image) {
            //     return [
            //         'id' => $image->id,
            //         'url' => $image->url,
            //         'order' => $image->order,
            //     ];
            // })->all(),
            'ratings_count' => $this->ratings_count ?? 0,
            'price' => $this->price,
            'unit' => $this->unit,
            'vendor' => $this->vendor,
            'category' => $this->category->name,
            'preparation_time' => $this->preparation_time ?? null,
        ];
    }
}
