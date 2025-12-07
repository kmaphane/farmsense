<?php

namespace Domains\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Domains\Auth\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasDefaultTenant;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the teams the user belongs to
     *
     * @return BelongsToMany<Team, $this, Pivot>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Get the current team the user is working with (relationship)
     *
     * @return BelongsTo<Team, $this>
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Set the current team context
     */
    public function setCurrentTeam(Team $team): void
    {
        if ($this->hasTeamAccess($team)) {
            $this->update(['current_team_id' => $team->id]);
        }
    }

    /**
     * Check if user has access to a specific team
     */
    public function hasTeamAccess(Team $team): bool
    {
        return $this->teams()->where('team_id', $team->id)->exists();
    }

    /**
     * Check if user is the owner of a team
     */
    public function isTeamOwner(Team $team): bool
    {
        return $this->id === $team->owner_id;
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the tenants (teams) this user belongs to for Filament.
     *
     * @return Collection<int, Team>
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    /**
     * Check if the user can access the given tenant.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->whereKey($tenant)->exists();
    }

    /**
     * Get the default tenant when logging in.
     */
    public function getDefaultTenant(Panel $panel): ?Model
    {
        return $this->currentTeam ?? $this->teams->first();
    }
}
