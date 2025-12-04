<?php

namespace Domains\Inventory\Enums;

enum ProductType: string
{
    case Feed = 'feed';
    case Medicine = 'medicine';
    case Packaging = 'packaging';
    case Equipment = 'equipment';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Feed => 'Feed',
            self::Medicine => 'Medicine',
            self::Packaging => 'Packaging',
            self::Equipment => 'Equipment',
            self::Other => 'Other',
        };
    }
}
