<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Carbon\Carbon;
use Domains\Broiler\Enums\BatchStatus;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class BatchData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $team_id,

        #[Required, StringType]
        public string $name,

        #[Required, StringType]
        public string $batch_number,

        #[Required, Date]
        public Carbon $start_date,

        #[Date]
        public ?Carbon $expected_end_date,

        #[Date]
        public ?Carbon $actual_end_date,

        #[Required]
        public BatchStatus $status,

        #[Required, IntegerType]
        public int $initial_quantity,

        #[IntegerType]
        public ?int $current_quantity,

        #[IntegerType]
        public ?int $supplier_id,

        #[Numeric]
        public ?float $target_weight_kg,

        #[Numeric]
        public ?float $average_weight_kg,
    ) {}
}
