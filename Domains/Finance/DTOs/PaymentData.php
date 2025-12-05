<?php

namespace Domains\Finance\DTOs;

use Domains\Shared\DTOs\BaseData;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class PaymentData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Numeric]
        public int $invoice_id,

        #[Required]
        #[Numeric]
        public int $amount,

        #[Required]
        #[In(['cash', 'bank_transfer', 'mobile_money', 'cheque', 'card'])]
        public string $payment_method,

        #[Nullable]
        #[Max(100)]
        public ?string $reference,

        #[Required]
        #[Date]
        public string $payment_date,

        #[Nullable]
        public ?string $notes,
    ) {}

    /**
     * Create from Filament form data
     */
    public static function fromFilament(array $data): static
    {
        return static::from([
            ...$data,
            'team_id' => static::getCurrentTeamId(),
        ]);
    }
}
