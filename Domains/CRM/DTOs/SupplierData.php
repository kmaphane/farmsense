<?php

namespace Domains\CRM\DTOs;

use Domains\Shared\DTOs\BaseData;
use Domains\Shared\Enums\SupplierCategory;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class SupplierData extends BaseData
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $name,

        #[Nullable]
        #[Email]
        #[Max(255)]
        public ?string $email,

        #[Nullable]
        #[Max(20)]
        public ?string $phone,

        #[Required]
        public SupplierCategory $category,

        #[Nullable]
        #[Numeric]
        #[Between(0, 5)]
        public ?float $performance_rating,

        #[Nullable]
        #[Numeric]
        public ?float $current_price_per_unit,

        #[Nullable]
        public ?string $notes,

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
            'is_active' => $data['is_active'] ?? true,
        ]);
    }
}
