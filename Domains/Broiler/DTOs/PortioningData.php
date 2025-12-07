<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class PortioningData extends Data
{
    public function __construct(
        #[Required, IntegerType]
        public int $team_id,

        #[Required, Date]
        public Carbon $portioning_date,

        #[Required, IntegerType, Min(1)]
        public int $whole_birds_used,

        #[Required, IntegerType, Min(1)]
        public int $packs_produced,

        #[Numeric, Min(0.1)]
        public float $pack_weight_kg = 0.5,

        #[StringType]
        public ?string $notes = null,
    ) {}

    /**
     * Calculate total weight produced in kg.
     */
    public function getTotalWeightProduced(): float
    {
        return $this->packs_produced * $this->pack_weight_kg;
    }

    /**
     * Calculate average yield per bird in kg.
     */
    public function getAverageYieldPerBird(): float
    {
        if ($this->whole_birds_used === 0) {
            return 0;
        }

        return round($this->getTotalWeightProduced() / $this->whole_birds_used, 2);
    }
}
