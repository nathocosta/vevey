<?php

declare(strict_types=1);

namespace Vevey\Catalog\Policies;

use Vevey\Catalog\Models\Attribute;
use Vevey\Permissions\Policies\ModelPolicy;

final class AttributePolicy extends ModelPolicy
{
    protected function model(): string
    {
        return Attribute::class;
    }
}
