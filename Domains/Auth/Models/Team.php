<?php

namespace Domains\Auth\Models;

use Domains\Auth\Factories\TeamFactory;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Team extends Model implements HasCurrentTenantLabel
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): TeamFactory
    {
        return TeamFactory::new();
    }

    protected $fillable = [
        'owner_id',
        'name',
        'subscription_plan',
    ];

    /**
     * Get the owner of the team
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users in this team
     *
     * @return BelongsToMany<User, $this, Pivot>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Add a user to the team with a specific role
     */
    public function addUser(User $user, int $roleId): void
    {
        // Only attach if not already a member
        if (! $this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->attach($user->id, ['role_id' => $roleId]);
        }
    }

    /**
     * Remove a user from the team
     */
    public function removeUser(User $user): void
    {
        $this->users()->detach($user->id);

        // Clear current team if it was the user's current team
        if ($user->current_team_id === $this->id) {
            $user->update(['current_team_id' => null]);
        }
    }

    /**
     * Change a user's role in the team
     */
    public function changeUserRole(User $user, int $roleId): void
    {
        if ($this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->updateExistingPivot($user->id, ['role_id' => $roleId]);
        }
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the label shown above the current tenant in the tenant switcher.
     */
    public function getCurrentTenantLabel(): string
    {
        return 'Active Farm';
    }
}
