<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Domains\Broiler\Enums\DiscrepancyReason;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SlaughterBatchSourceData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $batch_id,

        #[Required, IntegerType, Min(1)]
        public int $expected_quantity,

        #[Required, IntegerType, Min(0)]
        public int $actual_quantity,

        public ?DiscrepancyReason $discrepancy_reason = null,

        #[StringType]
        public ?string $discrepancy_notes = null,
    ) {}

    /**
     * Check if there's a discrepancy between expected and actual.
     */
    public function hasDiscrepancy(): bool
    {
        return $this->actual_quantity < $this->expected_quantity;
    }

    /**
     * Get the discrepancy amount.
     */
    public function getDiscrepancyAmount(): int
    {
        return max(0, $this->expected_quantity - $this->actual_quantity);
    }
}
