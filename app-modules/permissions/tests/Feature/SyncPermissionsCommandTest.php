<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;
use Vevey\Permissions\Models\Permission;
use Vevey\Permissions\Models\Role;

uses(TestCase::class, RefreshDatabase::class);

test('sync creates every role of the Roles enum on the web guard', function (): void {
    expect(Artisan::call('sync:permissions'))->toBe(0);

    expect(Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name')->all())
        ->toEqualCanonicalizing(array_map(fn (Roles $role): string => $role->value, Roles::cases()));
});

test('sync creates one permission per action for every model in the morph map', function (): void {
    expect(Artisan::call('sync:permissions'))->toBe(0);

    expect(Permission::query()->count())->toBe(count(Relation::morphMap()) * count(PermissionsEnum::cases()))
        ->and(Permission::query()->where('name', 'view_any_users')->value('resource_group'))->toBe('App')
        ->and(Permission::query()->where('name', 'update_roles')->value('resource_group'))->toBe('Permissions');
});

test('sync gives every permission to the super admin role and assigns it to admin@vevey.com', function (): void {
    $superAdmin = User::factory()->create(['email' => 'admin@vevey.com']);
    $otherUser = User::factory()->create();

    expect(Artisan::call('sync:permissions'))->toBe(0);

    expect(Role::findByName(Roles::SuperAdmin->value)->permissions)->toHaveCount(Permission::query()->count())
        ->and($superAdmin->fresh()?->hasRole(Roles::SuperAdmin))->toBeTrue()
        ->and($otherUser->fresh()?->roles)->toBeEmpty();
});

test('running sync twice keeps the same roles, permissions and super admin grants', function (): void {
    expect(Artisan::call('sync:permissions'))->toBe(0);
    $snapshot = fn (): array => [
        Role::query()->orderBy('name')->pluck('id')->all(),
        Permission::query()->orderBy('name')->pluck('id')->all(),
        Role::findByName(Roles::SuperAdmin->value)->permissions()->orderBy('name')->pluck('name')->all(),
    ];
    $first = $snapshot();

    expect(Artisan::call('sync:permissions'))->toBe(0);

    expect($snapshot())->toBe($first);
});
