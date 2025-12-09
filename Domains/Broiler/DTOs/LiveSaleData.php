<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class LiveSaleData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $team_id,

        #[Required, IntegerType]
        public int $batch_id,

        #[Required, Date]
        public Carbon $sale_date,

        #[Required, IntegerType, Min(1)]
        public int $quantity_sold,

        #[IntegerType]
        public ?int $unit_price_cents,

        #[IntegerType]
        public ?int $customer_id,

        #[StringType]
        public ?string $notes,

        #[Required, IntegerType]
        public int $recorded_by,
    ) {}
}
