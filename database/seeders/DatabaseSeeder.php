<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Vevey\Catalog\Database\Seeders\CatalogSeeder;
use Vevey\Permissions\Enums\Roles;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database for local use: roles and permissions, one staff user per role
     * (password `password`; admin@vevey.com is the super admin) and a sample catalog.
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

        foreach ([
            'dona@vevey.com' => Roles::Admin,
            'atendente@vevey.com' => Roles::Attendant,
            'logistica@vevey.com' => Roles::Logistics,
        ] as $email => $role) {
            User::factory()->create([
                'name' => $role->getLabel(),
                'email' => $email,
                'password' => 'password',
            ])->assignRole($role);
        }

        $this->call(CatalogSeeder::class);
    }
}
