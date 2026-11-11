<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
 * Feature tests of the app and every test of the modules run on the Laravel TestCase with a migrated
 * and seeded database (roles and permissions already synced; see Tests\TestCase).
 */
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', '../app-modules/*/tests');
