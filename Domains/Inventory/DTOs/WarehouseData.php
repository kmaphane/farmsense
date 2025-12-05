<?php

namespace Domains\Inventory\DTOs;

use Domains\Shared\DTOs\BaseData;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class WarehouseData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Max(255)]
        public string $name,

        #[Nullable]
        #[Max(500)]
        public ?string $location,

        #[Nullable]
        #[Numeric]
        #[Min(0)]
        public ?int $capacity,

        #[BooleanType]
        public bool $is_active = true,
    ) {}

    /**
     * Create from Filament form data
     */
    public static function fromFilament(array $data): static
    {
        return static::from([
            ...$data,
            'team_id' => static::getCurrentTeamId(),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
