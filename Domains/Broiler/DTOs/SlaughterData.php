<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class SlaughterData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $team_id,

        #[Required, Date]
        public Carbon $slaughter_date,

        #[Required, ArrayType]
        /** @var array<SlaughterBatchSourceData> */
        public array $batch_sources,

        #[Required, ArrayType]
        /** @var array<SlaughterYieldData> */
        public array $yields,

        #[Numeric]
        public ?float $total_live_weight_kg,

        #[Numeric]
        public ?float $total_dressed_weight_kg,

        #[StringType]
        public ?string $household_consumption_notes,

        #[StringType]
        public ?string $notes,

        #[Required, IntegerType]
        public int $recorded_by,
    ) {}
}
