<?php

declare(strict_types=1);

namespace Vevey\Catalog\Policies;

use Vevey\Catalog\Models\Category;
use Vevey\Permissions\Policies\ModelPolicy;

final class CategoryPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return Category::class;
    }
}
