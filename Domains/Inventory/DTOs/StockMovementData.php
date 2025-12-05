<?php

namespace Domains\Inventory\DTOs;

use Domains\Shared\DTOs\BaseData;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class StockMovementData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Numeric]
        public int $product_id,

        #[Required]
        #[Numeric]
        public int $warehouse_id,

        #[Required]
        #[Numeric]
        public int $quantity,

        #[Required]
        #[In(['in', 'out', 'adjustment', 'transfer'])]
        public string $movement_type,

        #[Required]
        #[Max(255)]
        public string $reason,

        #[Nullable]
        public ?string $notes,

        #[Nullable]
        #[Numeric]
        public ?int $recorded_by,
    ) {}

    /**
     * Create from Filament form data
     */
    public static function fromFilament(array $data): static
    {
        return static::from([
            ...$data,
            'team_id' => static::getCurrentTeamId(),
            'recorded_by' => auth()->id(),
        ]);
    }
}
