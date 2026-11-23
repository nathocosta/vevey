<?php

declare(strict_types=1);

namespace Vevey\Customers\Policies;

use Vevey\Customers\Models\Customer;
use Vevey\Permissions\Policies\ModelPolicy;

final class CustomerPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return Customer::class;
    }
}
