<?php

namespace Domains\Broiler\Resources;

use Domains\Inventory\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaughterFormDataResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'batches' => BatchResource::collection($this->resource['batches']),
            'products' => ProductResource::collection($this->resource['products']),
            'discrepancyReasons' => $this->resource['discrepancyReasons'],
            'suggestedDate' => $this->resource['suggestedDate'],
        ];
    }
}
