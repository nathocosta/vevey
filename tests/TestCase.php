<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * With RefreshDatabase the seeder runs once per process, right after the migrations, and every test
     * then runs inside a transaction on top of it: sync:permissions is not repeated per test.
     */
    protected bool $seed = true;

    protected string $seeder = PermissionSeeder::class;
}
