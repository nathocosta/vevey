<?php

declare(strict_types=1);

namespace Vevey\Inventory\DTOs;

final readonly class AdjustStockData
{
    public function __construct(
        public string $productId,
        public int $countedQty,
        public string $note,
        public ?string $userId = null,
        public ?int $reorderPoint = null,
    ) {}
}
