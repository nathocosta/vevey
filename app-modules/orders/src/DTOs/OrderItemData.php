<?php

declare(strict_types=1);

namespace Vevey\Orders\DTOs;

use Brick\Money\Money;
use Vevey\Orders\Models\OrderItem;

final readonly class OrderItemData
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $sku,
        public string $name,
        public int $quantity,
        public Money $unitPrice,
        public Money $discount,
        public Money $total,
    ) {}

    public static function fromModel(OrderItem $item): self
    {
        return new self(
            id: $item->id,
            productId: $item->product_id,
            sku: $item->sku,
            name: $item->name,
            quantity: $item->quantity,
            unitPrice: $item->unit_price_cents,
            discount: $item->discount_cents,
            total: $item->total_cents,
        );
    }
}
