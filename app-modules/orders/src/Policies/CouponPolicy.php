<?php

declare(strict_types=1);

namespace Vevey\Orders\Policies;

use Vevey\Orders\Models\Coupon;
use Vevey\Permissions\Policies\ModelPolicy;

final class CouponPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return Coupon::class;
    }
}
