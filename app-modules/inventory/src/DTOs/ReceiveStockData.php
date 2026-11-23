<?php

declare(strict_types=1);

namespace Vevey\Inventory\DTOs;

final readonly class ReceiveStockData
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public ?string $note = null,
        public ?string $userId = null,
        public ?int $reorderPoint = null,
    ) {}
}
