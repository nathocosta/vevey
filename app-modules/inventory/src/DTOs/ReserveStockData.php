<?php

declare(strict_types=1);

namespace Vevey\Inventory\DTOs;

use Carbon\CarbonImmutable;

final readonly class ReserveStockData
{
    /**
     * @param  list<StockItemData>  $items
     */
    public function __construct(
        public string $orderId,
        public array $items,
        public ?CarbonImmutable $expiresAt = null,
    ) {}
}
