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

        #[Required, IntegerType]
        public int $estimated_quantity,

        #[Required, IntegerType, Min(0)]
        public int $actual_quantity,
    ) {}
}
