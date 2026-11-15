<?php

declare(strict_types=1);

namespace Vevey\Catalog\Policies;

use Vevey\Catalog\Models\AttributeValue;
use Vevey\Permissions\Policies\ModelPolicy;

final class AttributeValuePolicy extends ModelPolicy
{
    protected function model(): string
    {
        return AttributeValue::class;
    }
}
