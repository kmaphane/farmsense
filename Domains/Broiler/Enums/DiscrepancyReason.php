<?php

declare(strict_types=1);

namespace Domains\Broiler\Enums;

enum DiscrepancyReason: string
{
    case Theft = 'theft';
    case Death = 'death';
    case Escape = 'escape';
    case CountingError = 'counting_error';
    case HouseholdConsumption = 'household_consumption';
    case GivenAway = 'given_away';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Theft => 'Theft',
            self::Death => 'Death/Disease',
            self::Escape => 'Escape',
            self::CountingError => 'Counting Error',
            self::HouseholdConsumption => 'Household Consumption',
            self::GivenAway => 'Given Away',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Theft => 'danger',
            self::Death => 'warning',
            self::Escape => 'info',
            self::CountingError => 'gray',
            self::HouseholdConsumption => 'success',
            self::GivenAway => 'primary',
            self::Other => 'gray',
        };
    }

    /**
     * Determine if this reason requires manager notification.
     */
    public function requiresNotification(): bool
    {
        return match ($this) {
            self::Theft, self::Escape => true,
            default => false,
        };
    }

    /**
     * Determine if this reason is considered suspicious/loss.
     */
    public function isSuspicious(): bool
    {
        return match ($this) {
            self::Theft, self::Escape, self::Other => true,
            default => false,
        };
    }
}
