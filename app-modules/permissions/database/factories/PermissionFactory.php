<?php

declare(strict_types=1);

namespace Vevey\Permissions\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Models\Permission;

/** @extends Factory<Permission> */
final class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(PermissionsEnum::cases());

        return [
            'name' => $action->value . '_' . fake()->unique()->word(),
            'guard_name' => 'web',
            'resource' => User::class,
            'resource_group' => 'App',
            'action' => $action,
        ];
    }
}
