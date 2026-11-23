<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Vevey\Permissions\Enums\Roles;

/**
 * Idempotent staff users seeder (works without Faker/--no-dev).
 * Creates admin@vevey.com (SuperAdmin) and one user per operational role (password: `password`).
 */
final class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            'admin@vevey.com' => ['name' => 'Super Admin', 'role' => Roles::SuperAdmin],
            'dona@vevey.com' => ['name' => 'Administrador', 'role' => Roles::Admin],
            'atendente@vevey.com' => ['name' => 'Atendente', 'role' => Roles::Attendant],
            'logistica@vevey.com' => ['name' => 'Logística', 'role' => Roles::Logistics],
        ];

        foreach ($staff as $email => $config) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $config['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles([$config['role']->value]);
        }
    }
}
