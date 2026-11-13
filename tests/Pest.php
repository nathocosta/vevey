<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Vevey\Permissions\Enums\Roles;

/*
 * Feature tests of the app and every test of the modules run on the Laravel TestCase with a migrated
 * and seeded database (roles and permissions already synced; see Tests\TestCase).
 */
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', '../app-modules/*/tests');

/**
 * A staff user holding the given role.
 */
function staffWithRole(Roles $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}
