<?php

namespace Domains\Inventory\DTOs;

use Domains\Shared\DTOs\BaseData;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class ProductData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Max(255)]
        public string $name,

        #[Nullable]
        public ?string $description,

        #[Required]
        #[In(['feed', 'medicine', 'packaging', 'equipment', 'other'])]
        public string $type,

        #[Required]
        #[Max(50)]
        public string $unit,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $quantity_on_hand,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $reorder_level,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $unit_cost,

        #[BooleanType]
        public bool $is_active,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $selling_price_cents,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $units_per_package,

        #[Nullable]
        public ?string $package_unit,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $yield_per_bird,
    ) {}

    /**
     * Create from Filament form data
     */
    public static function fromFilament(array $data): static
    {
        return static::from([
            ...$data,
            'team_id' => static::getCurrentTeamId(),
            'quantity_on_hand' => $data['quantity_on_hand'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
