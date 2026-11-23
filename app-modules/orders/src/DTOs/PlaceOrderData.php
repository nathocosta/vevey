<?php

declare(strict_types=1);

namespace Vevey\Orders\DTOs;

use Brick\Money\Money;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;

final readonly class PlaceOrderData
{
    /**
     * @param  list<PlaceOrderItemData>  $items
     */
    public function __construct(
        public OrderChannel $channel,
        public DeliveryMethod $deliveryMethod,
        public PaymentMethod $paymentMethod,
        public string $customerName,
        public array $items,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
        public ?string $customerId = null,
        public ?string $sellerId = null,
        public ?string $shippingMethod = null,
        public ?Money $shippingCost = null,
        public ?ShippingAddressData $shippingAddress = null,
        public ?string $couponCode = null,
        public ?string $notes = null,
    ) {}
}
