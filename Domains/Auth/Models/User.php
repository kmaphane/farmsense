<?php

namespace Domains\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

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
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Get the current team the user is working with
     *
     * @return Team|null
     */
    public function currentTeam(): ?Team
    {
        return $this->current_team_id ? Team::find($this->current_team_id) : null;
    }

    /**
     * Set the current team context
     *
     * @param Team $team
     * @return void
     */
    public function setCurrentTeam(Team $team): void
    {
        if ($this->hasTeamAccess($team)) {
            $this->update(['current_team_id' => $team->id]);
        }
    }

    /**
     * Check if user has access to a specific team
     *
     * @param Team $team
     * @return bool
     */
    public function hasTeamAccess(Team $team): bool
    {
        return $this->teams()->where('team_id', $team->id)->exists();
    }

    /**
     * Check if user is the owner of a team
     *
     * @param Team $team
     * @return bool
     */
    public function isTeamOwner(Team $team): bool
    {
        return $this->id === $team->owner_id;
    }
}
