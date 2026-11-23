<?php

declare(strict_types=1);

namespace Vevey\Orders\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Vevey\Orders\Enums\OrderChannel;

final readonly class OrderPlaced implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public string $orderId,
        public string $orderNumber,
        public OrderChannel $channel,
        public int $totalCents,
    ) {}
}
