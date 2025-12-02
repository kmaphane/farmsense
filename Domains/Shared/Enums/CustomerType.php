<?php

namespace Domains\Shared\Enums;

enum CustomerType: string
{
    case Wholesale = 'wholesale';
    case Retail = 'retail';

    public function label(): string
    {
        return match ($this) {
            self::Wholesale => 'Wholesale',
            self::Retail => 'Retail',
        };
    }
}
