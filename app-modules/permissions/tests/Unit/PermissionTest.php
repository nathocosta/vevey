<?php

declare(strict_types=1);

use App\Models\User;
use Tests\TestCase;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Models\Permission;
use Vevey\Permissions\Models\Role;

uses(TestCase::class);

test('permission names use the morph alias of the model', function (): void {
    expect(PermissionsEnum::ViewAny->buildPermissionFor(User::class))->toBe('view_any_users')
        ->and(PermissionsEnum::ForceDelete->buildPermissionFor(Role::class))->toBe('force_delete_roles');
});

test('formatted name joins group, model, action and name for the checkbox list', function (): void {
    $permission = new Permission([
        'name' => 'view_any_users',
        'resource' => User::class,
        'resource_group' => 'App',
        'action' => PermissionsEnum::ViewAny,
    ]);

    expect($permission->resource_model)->toBe('User')
        ->and($permission->formatted_name)->toBe('App-User-view_any-view_any_users');
});

test('roles and permissions live in the rbac tables', function (): void {
    expect((new Role)->getTable())->toBe('rbac_roles')
        ->and((new Permission)->getTable())->toBe('rbac_permissions');
});
