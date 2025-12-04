<?php

namespace Domains\Finance\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case Cheque = 'cheque';
    case Mobile = 'mobile';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Bank => 'Bank Transfer',
            self::Cheque => 'Cheque',
            self::Mobile => 'Mobile Money',
        };
    }
}
