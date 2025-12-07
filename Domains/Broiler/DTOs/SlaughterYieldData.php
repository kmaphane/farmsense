<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class SlaughterYieldData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $product_id,

        #[Required, IntegerType, Min(0)]
        public int $estimated_quantity,

        #[Required, IntegerType, Min(0)]
        public int $actual_quantity,
    ) {}

    /**
     * Get the household consumed amount (estimated - actual).
     */
    public function getHouseholdConsumed(): int
    {
        return max(0, $this->estimated_quantity - $this->actual_quantity);
    }
}
