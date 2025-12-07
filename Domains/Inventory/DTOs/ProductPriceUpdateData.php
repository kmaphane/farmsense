<?php

declare(strict_types=1);

namespace Domains\Inventory\DTOs;

use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class ProductPriceUpdateData extends Data
{
    public function __construct(
        #[Required, IntegerType, Min(1)]
        public int $new_price_cents,

        #[StringType]
        public ?string $reason = null,
    ) {}

    /**
     * Get the price in BWP (Pula).
     */
    public function getPriceInPula(): float
    {
        return $this->new_price_cents / 100;
    }

    /**
     * Format price as BWP string.
     */
    public function getFormattedPrice(): string
    {
        return 'P '.number_format($this->getPriceInPula(), 2);
    }
}
