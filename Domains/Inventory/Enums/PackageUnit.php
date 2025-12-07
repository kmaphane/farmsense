<?php

declare(strict_types=1);

namespace Domains\Inventory\Enums;

enum PackageUnit: string
{
    case Single = 'single';
    case Pack = 'pack';
    case Cup = 'cup';
    case Bag = 'bag';
    case Kilogram = 'kg';
    case Liter = 'liter';
    case Box = 'box';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single',
            self::Pack => 'Pack',
            self::Cup => 'Cup',
            self::Bag => 'Bag',
            self::Kilogram => 'Kilogram',
            self::Liter => 'Liter',
            self::Box => 'Box',
        };
    }

    public function abbreviation(): string
    {
        return match ($this) {
            self::Single => 'ea',
            self::Pack => 'pk',
            self::Cup => 'cup',
            self::Bag => 'bag',
            self::Kilogram => 'kg',
            self::Liter => 'L',
            self::Box => 'box',
        };
    }
}
