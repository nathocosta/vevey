<?php

declare(strict_types=1);

namespace Vevey\Customers\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Vevey\Customers\Models\Customer;
use Vevey\Customers\Models\CustomerAddress;
use Vevey\Customers\Models\CustomerIdentity;

final class CustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'customers' => Customer::class,
            'customer_identities' => CustomerIdentity::class,
            'customer_addresses' => CustomerAddress::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
