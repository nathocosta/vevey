<?php

declare(strict_types=1);

namespace Vevey\Inventory\Policies;

use Vevey\Inventory\Models\StockMovement;
use Vevey\Permissions\Policies\ModelPolicy;

final class StockMovementPolicy extends ModelPolicy
{
    protected function model(): string
    {
        return StockMovement::class;
    }
}
