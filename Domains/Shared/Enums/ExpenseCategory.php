<?php

namespace Domains\Shared\Enums;

enum ExpenseCategory: string
{
    case Feed = 'feed';
    case Labor = 'labor';
    case Utilities = 'utilities';
    case Equipment = 'equipment';
    case Maintenance = 'maintenance';
    case Healthcare = 'healthcare';
    case Transportation = 'transportation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Feed => 'Feed',
            self::Labor => 'Labor',
            self::Utilities => 'Utilities',
            self::Equipment => 'Equipment',
            self::Maintenance => 'Maintenance',
            self::Healthcare => 'Healthcare',
            self::Transportation => 'Transportation',
            self::Other => 'Other',
        };
    }
}
