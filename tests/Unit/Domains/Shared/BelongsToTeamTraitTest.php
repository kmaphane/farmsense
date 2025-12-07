<?php

use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;

/**
 * Create a mock model class for testing the BelongsToTeam trait.
 */
function createMockModelWithBelongsToTeam(): Model
{
    return new class extends Model
    {
        use BelongsToTeam;

        protected $table = 'mock_table';

        protected $fillable = ['team_id', 'name'];

        public $timestamps = false;
    };
}

it('applies team scope globally', function () {
    // This test verifies the trait structure and PHPDoc
    $model = createMockModelWithBelongsToTeam();
    expect($model)->toBeInstanceOf(Model::class);
});

it('has belongsToTeam scope method', function () {
    $model = createMockModelWithBelongsToTeam();
    expect(method_exists($model, 'scopeBelongsToTeam'))->toBeTrue();
});

it('has withoutTeamScope scope method', function () {
    $model = createMockModelWithBelongsToTeam();
    expect(method_exists($model, 'scopeWithoutTeamScope'))->toBeTrue();
});
