<?php

declare(strict_types=1);

namespace Vevey\Orders\Policies;

use Vevey\Orders\Models\Order;
use Vevey\Permissions\Policies\ModelPolicy;

final class OrderPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return Order::class;
    }
}
