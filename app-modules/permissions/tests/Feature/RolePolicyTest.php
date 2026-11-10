<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Vevey\Permissions\Enums\Roles;
use Vevey\Permissions\Models\Role;

test('only the super admin may manage roles', function (Roles $role, bool $allowed): void {
    $user = User::factory()->create();
    $user->assignRole($role);

    $record = Role::factory()->create();

    $gate = Gate::forUser($user);

    expect($gate->allows('viewAny', Role::class))->toBe($allowed)
        ->and($gate->allows('view', $record))->toBe($allowed)
        ->and($gate->allows('create', Role::class))->toBe($allowed)
        ->and($gate->allows('update', $record))->toBe($allowed)
        ->and($gate->allows('delete', $record))->toBe($allowed)
        ->and($gate->allows('restore', $record))->toBe($allowed)
        ->and($gate->allows('forceDelete', $record))->toBe($allowed);
})->with([
    'super admin' => [Roles::SuperAdmin, true],
    'admin' => [Roles::Admin, false],
    'attendant' => [Roles::Attendant, false],
    'logistics' => [Roles::Logistics, false],
]);
