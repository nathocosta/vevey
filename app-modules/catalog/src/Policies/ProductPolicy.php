<?php

declare(strict_types=1);

namespace Vevey\Catalog\Policies;

use Vevey\Catalog\Models\Product;
use Vevey\Permissions\Policies\ModelPolicy;

final class ProductPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return Product::class;
    }
}
