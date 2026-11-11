<?php

declare(strict_types=1);

namespace Vevey\Permissions\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vevey\Permissions\Models\Role;

/** @extends Factory<Role> */
final class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(2),
            'guard_name' => 'web',
        ];
    }
}
