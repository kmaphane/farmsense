<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class DailyLogData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $team_id,

        #[Required, IntegerType]
        public int $batch_id,

        #[Required, Date]
        public Carbon $log_date,

        #[Required, IntegerType]
        public int $mortality_count,

        #[Required, Numeric]
        public float $feed_consumed_kg,

        #[Numeric]
        public ?float $water_consumed_liters,

        #[Numeric]
        public ?float $temperature_celsius,

        #[Numeric]
        public ?float $humidity_percent,

        #[Numeric]
        public ?float $ammonia_ppm,

        #[StringType]
        public ?string $notes,

        #[Required, IntegerType]
        public int $recorded_by,
    ) {}
}
