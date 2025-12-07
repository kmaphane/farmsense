<?php

declare(strict_types=1);

namespace Domains\Broiler\DTOs;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SlaughterData extends Data
{
    /**
     * @param  DataCollection<int, SlaughterBatchSourceData>  $batch_sources
     * @param  DataCollection<int, SlaughterYieldData>  $yields
     */
    public function __construct(
        #[Required, IntegerType]
        public int $team_id,

        #[Required, Date]
        public Carbon $slaughter_date,

        #[Required, DataCollectionOf(SlaughterBatchSourceData::class)]
        public DataCollection $batch_sources,

        #[Required, DataCollectionOf(SlaughterYieldData::class)]
        public DataCollection $yields,

        #[StringType]
        public ?string $notes = null,
    ) {}

    /**
     * Get total birds from all batch sources.
     */
    public function getTotalBirds(): int
    {
        return $this->batch_sources->sum('actual_quantity');
    }

    /**
     * Check if any batch source has a discrepancy.
     */
    public function hasDiscrepancies(): bool
    {
        return $this->batch_sources->contains(fn (SlaughterBatchSourceData $source) => $source->hasDiscrepancy());
    }

    /**
     * Convert batch sources to array format for action.
     *
     * @return array<int, array{expected: int, actual: int, reason?: string, notes?: string}>
     */
    public function toBatchSourcesArray(): array
    {
        $result = [];

        foreach ($this->batch_sources as $source) {
            $result[$source->batch_id] = [
                'expected' => $source->expected_quantity,
                'actual' => $source->actual_quantity,
                'reason' => $source->discrepancy_reason?->value,
                'notes' => $source->discrepancy_notes,
            ];
        }

        return $result;
    }

    /**
     * Convert yields to array format for action.
     *
     * @return array<int, array{estimated: int, actual: int}>
     */
    public function toYieldsArray(): array
    {
        $result = [];

        foreach ($this->yields as $yield) {
            $result[$yield->product_id] = [
                'estimated' => $yield->estimated_quantity,
                'actual' => $yield->actual_quantity,
            ];
        }

        return $result;
    }
}
