<?php

declare(strict_types=1);

namespace Vevey\Inventory\Policies;

use Vevey\Inventory\Models\StockLevel;
use Vevey\Permissions\Policies\ModelPolicy;

final class StockLevelPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return StockLevel::class;
    }
}
