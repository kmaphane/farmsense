<?php

declare(strict_types=1);

namespace Domains\Broiler\Enums;

enum BatchStatus: string
{
    case Planned = 'planned';
    case Active = 'active';
    case Harvesting = 'harvesting';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Active => 'Active',
            self::Harvesting => 'Harvesting',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Planned => 'gray',
            self::Active => 'success',
            self::Harvesting => 'warning',
            self::Closed => 'info',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Planned => $status === self::Active,
            self::Active => $status === self::Harvesting,
            self::Harvesting => $status === self::Closed,
            self::Closed => false,
        };
    }
}
