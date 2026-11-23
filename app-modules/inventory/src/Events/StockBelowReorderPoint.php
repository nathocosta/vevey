<?php

declare(strict_types=1);

namespace Vevey\Inventory\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

final readonly class StockBelowReorderPoint implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public string $productId,
        public int $onHand,
        public int $reorderPoint,
    ) {}
}
