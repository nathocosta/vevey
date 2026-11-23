<?php

declare(strict_types=1);

namespace Vevey\Orders\DTOs;

use Brick\Money\Money;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;
use Vevey\Orders\Models\OrderItem;

final readonly class OrderData
{
    /**
     * @param  list<OrderItemData>  $items
     */
    public function __construct(
        public string $id,
        public string $number,
        public OrderChannel $channel,
        public PaymentStatus $paymentStatus,
        public FulfillmentStatus $fulfillmentStatus,
        public DeliveryMethod $deliveryMethod,
        public PaymentMethod $paymentMethod,
        public string $customerName,
        public ?string $shippingMethod,
        public Money $subtotal,
        public Money $discount,
        public Money $shipping,
        public Money $total,
        public array $items,
    ) {}

    public static function fromModel(Order $order, PaymentMethod $paymentMethod): self
    {
        return new self(
            id: $order->id,
            number: $order->number,
            channel: $order->channel,
            paymentStatus: $order->payment_status,
            fulfillmentStatus: $order->fulfillment_status,
            deliveryMethod: $order->delivery_method,
            paymentMethod: $paymentMethod,
            customerName: $order->customer_name,
            shippingMethod: $order->shipping_method,
            subtotal: $order->subtotal_cents,
            discount: $order->discount_cents,
            shipping: $order->shipping_cents,
            total: $order->total_cents,
            items: array_values($order->items->map(static fn (OrderItem $item): OrderItemData => OrderItemData::fromModel($item))->all()),
        );
    }
}
