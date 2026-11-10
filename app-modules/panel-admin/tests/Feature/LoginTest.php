<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Vevey\PanelAdmin\Filament\Auth\Login;

uses(TestCase::class, RefreshDatabase::class);

test('the login form comes filled with the seeded super admin locally', function (): void {
    app()->instance('env', 'local');

    Livewire::test(Login::class)
        ->assertSchemaStateSet([
            'email' => 'admin@vevey.com',
            'password' => 'password',
        ], 'form');
});

test('the login form starts empty outside local', function (string $environment): void {
    app()->instance('env', $environment);

    Livewire::test(Login::class)
        ->assertSchemaStateSet([
            'email' => null,
            'password' => null,
        ], 'form');
})->with(['production', 'staging', 'testing']);
