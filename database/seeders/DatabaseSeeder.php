<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Vevey\Permissions\Enums\Roles;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database: roles and permissions, then the dev team's super admin
     * (admin@vevey.com / password) to log in at /admin locally.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@vevey.com',
            'password' => 'password',
        ]);

        $superAdmin->assignRole(Roles::SuperAdmin);
    }
}
