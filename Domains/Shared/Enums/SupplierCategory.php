<?php

namespace Domains\Shared\Enums;

enum SupplierCategory: string
{
    case Feed = 'feed';
    case Chicks = 'chicks';
    case Meds = 'meds';

    public function label(): string
    {
        return match ($this) {
            self::Feed => 'Feed',
            self::Chicks => 'Chicks',
            self::Meds => 'Medications',
        };
    }
}
