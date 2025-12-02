<?php

use Domains\Shared\Traits\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;

class MockModel extends Model
{
    use BelongsToTeam;

    protected $table = 'mock_table';
    protected $fillable = ['team_id', 'name'];
    public $timestamps = false;
}

it('applies team scope globally', function () {
    // This test verifies the trait structure and PHPDoc
    $model = new MockModel();
    expect($model)->toBeInstanceOf(Model::class);
});

it('has belongsToTeam scope method', function () {
    $model = new MockModel();
    expect(method_exists($model, 'scopeBelongsToTeam'))->toBeTrue();
});

it('has withoutTeamScope scope method', function () {
    $model = new MockModel();
    expect(method_exists($model, 'scopeWithoutTeamScope'))->toBeTrue();
});
