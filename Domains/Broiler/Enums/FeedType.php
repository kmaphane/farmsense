<?php

declare(strict_types=1);

namespace Domains\Broiler\Enums;

enum FeedType: string
{
    case Starter = 'starter';
    case Grower = 'grower';
    case Finisher = 'finisher';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Grower => 'Grower',
            self::Finisher => 'Finisher',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Starter => 'info',
            self::Grower => 'warning',
            self::Finisher => 'success',
        };
    }

    /**
     * Get the age range (in days) for this feed type.
     *
     * @return array{min: int, max: int}
     */
    public function ageRange(): array
    {
        return match ($this) {
            self::Starter => ['min' => 1, 'max' => 10],
            self::Grower => ['min' => 11, 'max' => 24],
            self::Finisher => ['min' => 25, 'max' => 42],
        };
    }

    /**
     * Get recommended feed type based on bird age in days.
     */
    public static function forAge(int $ageDays): self
    {
        return match (true) {
            $ageDays <= 10 => self::Starter,
            $ageDays <= 24 => self::Grower,
            default => self::Finisher,
        };
    }

    /**
     * Get protein percentage recommendation for this feed type.
     */
    public function proteinPercentage(): float
    {
        return match ($this) {
            self::Starter => 23.0,
            self::Grower => 21.0,
            self::Finisher => 19.0,
        };
    }
}
