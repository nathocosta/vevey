<?php

declare(strict_types=1);

namespace Vevey\Inventory\DTOs;

final readonly class RecordSaleData
{
    /**
     * @param  list<StockItemData>  $items
     */
    public function __construct(
        public string $orderId,
        public array $items,
        public ?string $userId = null,
        public bool $allowNegative = false,
        public ?string $note = null,
    ) {}
}
