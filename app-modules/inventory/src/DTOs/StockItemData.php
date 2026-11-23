<?php

declare(strict_types=1);

namespace Vevey\Inventory\DTOs;

final readonly class StockItemData
{
    public function __construct(
        public string $productId,
        public int $quantity,
    ) {}
}
