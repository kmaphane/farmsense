<?php

namespace Domains\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Domains\Auth\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Domains\Auth\Factories\UserFactory::new();
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
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Get the current team the user is working with (relationship)
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
}
