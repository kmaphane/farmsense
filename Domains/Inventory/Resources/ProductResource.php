<?php

namespace Domains\Inventory\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'type' => $this->type?->value,
            'yield_per_bird' => $this->when(
                isset($this->yield_per_bird),
                fn () => (float) $this->yield_per_bird
            ),
            'units_per_package' => $this->when(
                isset($this->units_per_package),
                fn () => (float) $this->units_per_package
            ),
            'package_unit' => $this->package_unit?->value,
            'quantity_on_hand' => $this->when(
                isset($this->quantity_on_hand),
                fn () => (float) $this->quantity_on_hand
            ),
        ];
    }
}
