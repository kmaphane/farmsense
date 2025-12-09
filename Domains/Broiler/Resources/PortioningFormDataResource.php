<?php

namespace Domains\Broiler\Resources;

use Domains\Inventory\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortioningFormDataResource extends JsonResource
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
            'wholeChickenStock' => $this->resource['wholeChickenStock']
                ? new ProductResource($this->resource['wholeChickenStock'])
                : null,
            'chickenPiecesProduct' => $this->resource['chickenPiecesProduct']
                ? new ProductResource($this->resource['chickenPiecesProduct'])
                : null,
            'suggestedDate' => $this->resource['suggestedDate'],
            'defaultPackWeight' => $this->resource['defaultPackWeight'],
        ];
    }
}
