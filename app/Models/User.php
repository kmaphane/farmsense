<?php

namespace App\Models;

use Domains\Auth\Models\User as DomainUser;

/**
 * User Model Alias
 *
 * This is an alias to Domains\Auth\Models\User for backwards compatibility
 * with Laravel's Fortify and other components that expect App\Models\User.
 *
 * All user logic is defined in the domain model.
 */
class User extends DomainUser
{
}
