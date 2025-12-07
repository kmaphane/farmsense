<?php

namespace Tests;

use Domains\Auth\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for tests that need them
        if (method_exists($this, 'seedRolesAndPermissions') || $this->shouldSeedRoles()) {
            $this->seed(RoleAndPermissionSeeder::class);
        }
    }

    /**
     * Determine if roles should be seeded automatically.
     */
    protected function shouldSeedRoles(): bool
    {
        return false;
    }
}
