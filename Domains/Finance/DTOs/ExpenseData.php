<?php

namespace Domains\Finance\DTOs;

use Domains\Shared\DTOs\BaseData;
use Domains\Shared\Enums\ExpenseCategory;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class ExpenseData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Numeric]
        public int $amount,

        #[Required]
        #[Max(3)]
        public string $currency = 'BWP',

        #[Required]
        public ExpenseCategory $category,

        #[Required]
        public string $description,

        #[Nullable]
        #[Max(255)]
        public ?string $allocatable_type,

        #[Nullable]
        #[Numeric]
        public ?int $allocatable_id,

        #[Nullable]
        #[ArrayType]
        public ?array $ocr_data,

        #[Nullable]
        #[Max(255)]
        public ?string $receipt_path,
    ) {}

    /**
     * Create from Filament form data
     */
    public static function fromFilament(array $data): static
    {
        return static::from([
            ...$data,
            'team_id' => static::getCurrentTeamId(),
            'currency' => $data['currency'] ?? 'BWP',
        ]);
    }

    /**
     * Set allocatable polymorphic relationship
     */
    public function withAllocatable(string $type, int $id): static
    {
        $this->allocatable_type = $type;
        $this->allocatable_id = $id;

        return $this;
    }
}
