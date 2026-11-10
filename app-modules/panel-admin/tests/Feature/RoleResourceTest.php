<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use Vevey\PanelAdmin\Filament\Resources\Roles\Pages\CreateRole;
use Vevey\PanelAdmin\Filament\Resources\Roles\Pages\EditRole;
use Vevey\PanelAdmin\Filament\Resources\Roles\Pages\ListRoles;
use Vevey\PanelAdmin\Filament\Resources\Roles\RoleResource;
use Vevey\Permissions\Enums\Roles;
use Vevey\Permissions\Models\Permission;
use Vevey\Permissions\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function staffWithRole(Roles $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('a user without a role cannot enter the admin panel', function (): void {
    actingAs(User::factory()->create());

    get('/admin')->assertForbidden();
});

test('staff with a role can enter the admin panel', function (): void {
    actingAs(staffWithRole(Roles::Attendant));

    get('/admin')->assertOk();
});

test('the roles page is forbidden to everyone but the super admin', function (Roles $role): void {
    actingAs(staffWithRole($role));

    get(RoleResource::getUrl('index'))->assertForbidden();
})->with([Roles::Admin, Roles::Attendant, Roles::Logistics]);

test('the super admin lists the roles', function (): void {
    actingAs(staffWithRole(Roles::SuperAdmin));

    Livewire::test(ListRoles::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Role::query()->get());
});

test('the super admin creates a role with permissions', function (): void {
    actingAs(staffWithRole(Roles::SuperAdmin));
    $permission = Permission::findByName('view_any_users');

    Livewire::test(CreateRole::class)
        ->fillForm([
            'name' => 'estoquista',
            'guard_name' => 'web',
            'permissions' => [$permission->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Role::findByName('estoquista')->permissions->pluck('name')->all())->toBe(['view_any_users']);
});

test('the super admin edits the permissions of a role', function (): void {
    actingAs(staffWithRole(Roles::SuperAdmin));
    $role = Role::findByName(Roles::Logistics->value);
    $permission = Permission::findByName('view_users');

    Livewire::test(EditRole::class, ['record' => $role->getKey()])
        ->assertOk()
        ->fillForm(['permissions' => [$permission->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->fresh()?->permissions->pluck('name')->all())->toBe(['view_users']);
});

test('the resource labels resolve from the module translations', function (string $locale, string $label): void {
    app()->setLocale($locale);

    expect(RoleResource::getNavigationLabel())->toBe($label)
        ->and(RoleResource::getPluralModelLabel())->not->toStartWith('panel-admin::');
})->with([
    'en' => ['en', 'Roles and permissions'],
    'pt_BR' => ['pt_BR', 'Papéis e permissões'],
]);
