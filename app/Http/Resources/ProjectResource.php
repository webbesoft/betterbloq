<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'budget' => $this->budget,
            'start_date' => $this->start_date,
            'target_completion_date' => $this->target_completion_date,
        ];
    }
}
