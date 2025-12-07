<?php

namespace Domains\Finance\DTOs;

use Domains\Shared\DTOs\BaseData;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;

class InvoiceData extends BaseData
{
    public function __construct(
        #[Nullable]
        #[Numeric]
        public ?int $team_id,

        #[Required]
        #[Numeric]
        public int $customer_id,

        #[Required]
        #[Max(50)]
        public string $invoice_number,

        #[Required]
        #[In(['draft', 'sent', 'paid', 'overdue', 'cancelled'])]
        public string $status,

        #[Required]
        #[Numeric]
        public int $subtotal,

        #[Required]
        #[Numeric]
        public int $tax_amount,

        #[Required]
        #[Numeric]
        public int $total_amount,

        #[Nullable]
        public ?string $description,

        #[Nullable]
        #[Date]
        public ?string $due_date,

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
            'status' => $data['status'] ?? 'draft',
        ]);
    }

    /**
     * Calculate totals from line items
     */
    public function withCalculatedTotals(int $subtotal, int $taxAmount): static
    {
        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal + $taxAmount;

        return $this;
    }
}
