<?php

namespace Domains\CRM\DTOs;

use Domains\Shared\DTOs\BaseData;
use Domains\Shared\Enums\CustomerType;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class CustomerData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Max(255)]
        public string $name,

        #[Nullable]
        #[Email]
        #[Max(255)]
        public ?string $email,

        #[Nullable]
        #[Max(20)]
        public ?string $phone,

        #[Required]
        public CustomerType $type,

        #[Nullable]
        #[Numeric]
        public ?int $credit_limit,

        #[Nullable]
        #[Numeric]
        public ?int $payment_terms,

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
