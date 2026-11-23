<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Vevey\Catalog\Models\Product;
use Vevey\Inventory\Actions\AdjustStock;
use Vevey\Inventory\Actions\ReceiveStock;
use Vevey\Inventory\DTOs\AdjustStockData;
use Vevey\Inventory\DTOs\ReceiveStockData;
use Vevey\Inventory\Enums\StockMovementReason;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Orders\Actions\PlaceOrder;
use Vevey\Orders\Actions\UpdateOrderStatus;
use Vevey\Orders\DTOs\PlaceOrderData;
use Vevey\Orders\DTOs\PlaceOrderItemData;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Exceptions\OrderPlacementException;
use Vevey\Orders\Models\Order;

uses(RefreshDatabase::class);

it('runs the full ADR 2 two-day stock simulation cleanly', function (): void {
    $product = Product::factory()->create(['is_active' => true]);

    // 1. Purchase of 20 units -> on_hand = 20
    app(ReceiveStock::class)->handle(new ReceiveStockData(
        productId: $product->id,
        quantity: 20,
        note: 'Compra fornecedor',
    ));

    // 2. Web order A (3 units) -> reserved = 3, on_hand = 20 -> paid -> sale -3, on_hand = 17
    $orderA = app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Web,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::Pix,
        customerName: 'Cliente A',
        items: [new PlaceOrderItemData(productId: $product->id, quantity: 3)],
    ));

    $level = StockLevel::query()->findOrFail($product->id);
    expect($level->on_hand)->toBe(20)->and($level->reserved)->toBe(3);

    app(UpdateOrderStatus::class)->handle(
        order: Order::query()->findOrFail($orderA->id),
        paymentStatus: PaymentStatus::Paid,
    );

    $level->refresh();
    expect($level->on_hand)->toBe(17)->and($level->reserved)->toBe(0);

    // 3. Web order B (5 units) -> reserved = 5 -> cancelled -> released, ledger untouched, on_hand = 17
    $orderB = app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Web,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::Pix,
        customerName: 'Cliente B',
        items: [new PlaceOrderItemData(productId: $product->id, quantity: 5)],
    ));

    app(UpdateOrderStatus::class)->handle(
        order: Order::query()->findOrFail($orderB->id),
        fulfillmentStatus: FulfillmentStatus::Cancelled,
    );

    $level->refresh();
    expect($level->on_hand)->toBe(17)->and($level->reserved)->toBe(0);

    // 4. POS counter sale (2 units) -> direct sale -2, on_hand = 15
    app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Pos,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::Cash,
        customerName: 'Balcão',
        items: [new PlaceOrderItemData(productId: $product->id, quantity: 2)],
    ));

    expect($level->refresh()->on_hand)->toBe(15);

    // 5. Physical count finds 14 -> adjustment -1 ("quebra"), on_hand = 14
    app(AdjustStock::class)->handle(new AdjustStockData(
        productId: $product->id,
        countedQty: 14,
        note: 'quebra',
    ));

    expect($level->refresh()->on_hand)->toBe(14)
        ->and(StockMovement::query()->where('product_id', $product->id)->where('reason', StockMovementReason::Adjustment)->value('qty'))->toBe(-1);

    // 6. Web order exceeding stock is blocked; POS order is allowed to go negative (ADR 5)
    expect(fn () => app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Web,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::Pix,
        customerName: 'Sem saldo',
        items: [new PlaceOrderItemData(productId: $product->id, quantity: 20)],
    )))->toThrow(OrderPlacementException::class);

    app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Pos,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::Cash,
        customerName: 'Balcão Negativo',
        items: [new PlaceOrderItemData(productId: $product->id, quantity: 16)],
    ));

    expect($level->refresh()->on_hand)->toBe(-2);

    // 7. stock:rebuild recomputes the exact same balance from the ledger
    Artisan::call('stock:rebuild');
    expect($level->refresh()->on_hand)->toBe(-2);
});
