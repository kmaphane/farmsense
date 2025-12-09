<?php

namespace Domains\Broiler\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchFormDataResource extends JsonResource
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
            'suppliers' => $this->resource['suppliers']->map(fn ($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ]),
            'suggestedBatchNumber' => $this->resource['suggestedBatchNumber'],
            'suggestedStartDate' => $this->resource['suggestedStartDate'],
        ];
    }
}
