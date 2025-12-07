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

        #[IntegerType, Min(1)]
        public ?int $unit_price_cents = null,

        #[IntegerType]
        public ?int $customer_id = null,

        #[StringType]
        public ?string $notes = null,
    ) {}

    /**
     * Calculate total amount in cents.
     * Returns null if unit price is not set.
     */
    public function getTotalAmountCents(): ?int
    {
        if ($this->unit_price_cents === null) {
            return null;
        }

        return $this->quantity_sold * $this->unit_price_cents;
    }

    /**
     * Format total amount as BWP string.
     */
    public function getFormattedTotal(): ?string
    {
        $total = $this->getTotalAmountCents();

        if ($total === null) {
            return null;
        }

        return 'P '.number_format($total / 100, 2);
    }
}
