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
            'additional_images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'url' => $image->url,
                    'order' => $image->order,
                ];
            })->all(),
            'ratings_count' => $this->ratings_count ?? 0,
            'price' => $this->price,
            'unit' => $this->unit,
            'vendor' => $this->vendor,
            'category' => $this->category->name,
            'storable' => $this->storable,
            'is_stackable' => $this->is_stackable,
            'storage_unit_of_measure' => $this->storage_unit_of_measure,
            'default_length' => $this->default_length,
            'default_width' => $this->default_width,
            'default_height' => $this->default_height,
            'delivery_time' => $this->delivery_time ?? null,
            'average_rating' => $this->ratings_avg_rating ?? 0,
        ];
    }
}
