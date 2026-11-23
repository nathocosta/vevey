<?php

declare(strict_types=1);

namespace Vevey\Orders\DTOs;

final readonly class PlaceOrderItemData
{
    public function __construct(
        public string $productId,
        public int $quantity,
    ) {}
}
