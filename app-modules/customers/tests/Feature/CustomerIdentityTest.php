<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vevey\Customers\Actions\FindCustomerByPhone;
use Vevey\Customers\Actions\FindOrCreateCustomerByContact;
use Vevey\Customers\Actions\RetireAndReplaceIdentity;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Enums\IdentityType;
use Vevey\Customers\Models\Customer;

uses(RefreshDatabase::class);

it('creates a passwordless customer, retires old phone on change, and still finds retired number (ADR 7)', function (): void {
    $created = app(FindOrCreateCustomerByContact::class)->handle(
        name: 'Carla Mendes',
        email: 'Carla@Vevey.test',
        phone: '(11) 99999-1111',
        source: IdentitySource::Whatsapp,
    );

    $customer = Customer::query()->findOrFail($created->id);
    expect($customer->password)->toBeNull()
        ->and($customer->primaryPhone())->toBe('+5511999991111');

    // Troca de número: aposenta o antigo e ativa o novo
    app(RetireAndReplaceIdentity::class)->handle(
        customerId: $customer->id,
        type: IdentityType::Whatsapp,
        newRawValue: '(11) 98888-2222',
        source: IdentitySource::Whatsapp,
    );

    expect($customer->refresh()->primaryPhone())->toBe('+5511988882222');

    // Busca pelo número antigo encontra o cliente com isRetired = true
    $oldMatches = app(FindCustomerByPhone::class)->handle('11999991111');
    expect($oldMatches)->toHaveCount(1)
        ->and($oldMatches[0]->customer->id)->toBe($customer->id)
        ->and($oldMatches[0]->isRetired)->toBeTrue();

    // Busca pelo número novo encontra o cliente com isRetired = false
    $newMatches = app(FindCustomerByPhone::class)->handle('11988882222');
    expect($newMatches)->toHaveCount(1)
        ->and($newMatches[0]->isRetired)->toBeFalse();
});
