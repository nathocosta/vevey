<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Roles and permissions only. Also the seeder of every test: RefreshDatabase runs it once per process, after migrating.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('sync:permissions');
    }
}
