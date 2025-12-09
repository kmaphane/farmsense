<?php

namespace Domains\Broiler\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
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
            'batch_number' => $this->batch_number,
            'current_quantity' => $this->current_quantity,
            'age_in_days' => $this->age_in_days,
        ];
    }
}
