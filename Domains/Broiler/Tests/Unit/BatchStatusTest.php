<?php

use Domains\Broiler\Enums\BatchStatus;

describe('BatchStatus enum values', function () {
    it('has correct enum values', function () {
        expect(BatchStatus::Planned->value)->toBe('planned');
        expect(BatchStatus::Active->value)->toBe('active');
        expect(BatchStatus::Harvesting->value)->toBe('harvesting');
        expect(BatchStatus::Closed->value)->toBe('closed');
    });

    it('has all expected cases', function () {
        $cases = BatchStatus::cases();

        expect($cases)->toHaveCount(4);
        expect(collect($cases)->pluck('value')->toArray())->toBe([
            'planned',
            'active',
            'harvesting',
            'closed',
        ]);
    });
});

describe('label method', function () {
    it('returns human-readable labels', function () {
        expect(BatchStatus::Planned->label())->toBe('Planned');
        expect(BatchStatus::Active->label())->toBe('Active');
        expect(BatchStatus::Harvesting->label())->toBe('Harvesting');
        expect(BatchStatus::Closed->label())->toBe('Closed');
    });
});

describe('color method', function () {
    it('returns appropriate colors for each status', function () {
        expect(BatchStatus::Planned->color())->toBe('gray');
        expect(BatchStatus::Active->color())->toBe('success');
        expect(BatchStatus::Harvesting->color())->toBe('warning');
        expect(BatchStatus::Closed->color())->toBe('info');
    });
});

describe('canTransitionTo method', function () {
    it('allows Planned to transition to Active only', function () {
        $planned = BatchStatus::Planned;

        expect($planned->canTransitionTo(BatchStatus::Active))->toBeTrue();
        expect($planned->canTransitionTo(BatchStatus::Harvesting))->toBeFalse();
        expect($planned->canTransitionTo(BatchStatus::Closed))->toBeFalse();
        expect($planned->canTransitionTo(BatchStatus::Planned))->toBeFalse();
    });

    it('allows Active to transition to Harvesting only', function () {
        $active = BatchStatus::Active;

        expect($active->canTransitionTo(BatchStatus::Planned))->toBeFalse();
        expect($active->canTransitionTo(BatchStatus::Harvesting))->toBeTrue();
        expect($active->canTransitionTo(BatchStatus::Closed))->toBeFalse();
        expect($active->canTransitionTo(BatchStatus::Active))->toBeFalse();
    });

    it('allows Harvesting to transition to Closed only', function () {
        $harvesting = BatchStatus::Harvesting;

        expect($harvesting->canTransitionTo(BatchStatus::Planned))->toBeFalse();
        expect($harvesting->canTransitionTo(BatchStatus::Active))->toBeFalse();
        expect($harvesting->canTransitionTo(BatchStatus::Closed))->toBeTrue();
        expect($harvesting->canTransitionTo(BatchStatus::Harvesting))->toBeFalse();
    });

    it('does not allow Closed to transition to any status', function () {
        $closed = BatchStatus::Closed;

        expect($closed->canTransitionTo(BatchStatus::Planned))->toBeFalse();
        expect($closed->canTransitionTo(BatchStatus::Active))->toBeFalse();
        expect($closed->canTransitionTo(BatchStatus::Harvesting))->toBeFalse();
        expect($closed->canTransitionTo(BatchStatus::Closed))->toBeFalse();
    });

    it('enforces forward-only transitions', function () {
        // No backward transitions allowed
        expect(BatchStatus::Active->canTransitionTo(BatchStatus::Planned))->toBeFalse();
        expect(BatchStatus::Harvesting->canTransitionTo(BatchStatus::Active))->toBeFalse();
        expect(BatchStatus::Closed->canTransitionTo(BatchStatus::Harvesting))->toBeFalse();
    });
});

describe('creating from value', function () {
    it('can create enum from string value', function () {
        expect(BatchStatus::from('planned'))->toBe(BatchStatus::Planned);
        expect(BatchStatus::from('active'))->toBe(BatchStatus::Active);
        expect(BatchStatus::from('harvesting'))->toBe(BatchStatus::Harvesting);
        expect(BatchStatus::from('closed'))->toBe(BatchStatus::Closed);
    });

    it('throws exception for invalid value', function () {
        expect(fn () => BatchStatus::from('invalid'))
            ->toThrow(ValueError::class);
    });

    it('can try to create enum from string value', function () {
        expect(BatchStatus::tryFrom('planned'))->toBe(BatchStatus::Planned);
        expect(BatchStatus::tryFrom('invalid'))->toBeNull();
    });
});
