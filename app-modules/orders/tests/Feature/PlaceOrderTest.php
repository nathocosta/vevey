<?php

declare(strict_types=1);

use App\Casts\MoneyCast;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Vevey\Catalog\Models\Product;
use Vevey\Orders\Actions\PlaceOrder;
use Vevey\Orders\Actions\UpdateOrderStatus;
use Vevey\Orders\DTOs\PlaceOrderData;
use Vevey\Orders\DTOs\PlaceOrderItemData;
use Vevey\Orders\DTOs\ShippingAddressData;
use Vevey\Orders\Enums\AdjustmentType;
use Vevey\Orders\Enums\CouponType;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Events\OrderPlaced;
use Vevey\Orders\Exceptions\OrderPlacementException;
use Vevey\Orders\Models\Coupon;
use Vevey\Orders\Models\Order;

uses(RefreshDatabase::class);

it('places a web order with Pix discount, allocates discount across lines, snapshots products and records history', function (): void {
    Event::fake([OrderPlaced::class]);

    $p1 = Product::factory()->create([
        'sku' => 'DOC-KITKAT-CX24',
        'name' => 'KitKat Caixa 24un',
        'price_cents' => Money::ofMinor(8990, MoneyCast::CURRENCY),
        'promo_price_cents' => Money::ofMinor(7990, MoneyCast::CURRENCY),
        'is_active' => true,
    ]);

    $p2 = Product::factory()->create([
        'sku' => 'BAL-MET-9-AZ',
        'name' => 'Balão Metalizado 9 Azul',
        'price_cents' => Money::ofMinor(1200, MoneyCast::CURRENCY),
        'promo_price_cents' => null,
        'is_active' => true,
    ]);

    $action = app(PlaceOrder::class);

    $result = $action->handle(new PlaceOrderData(
        channel: OrderChannel::Web,
        deliveryMethod: DeliveryMethod::Delivery,
        paymentMethod: PaymentMethod::Pix,
        customerName: 'Maria Oliveira',
        customerEmail: 'maria@vevey.test',
        customerPhone: '11999998888',
        shippingMethod: 'SEDEX — 2 dias úteis',
        shippingCost: Money::ofMinor(1850, MoneyCast::CURRENCY),
        shippingAddress: new ShippingAddressData(
            cep: '01310-100',
            street: 'Av. Paulista',
            number: '1000',
            neighborhood: 'Bela Vista',
            city: 'São Paulo',
            state: 'SP',
        ),
        items: [
            new PlaceOrderItemData(productId: $p1->id, quantity: 1),
            new PlaceOrderItemData(productId: $p2->id, quantity: 2),
        ],
    ));

    // Subtotal: 7990 + (1200 * 2) = 10390 cents
    // Pix 5%: round(10390 * 0.05) = 520 cents
    // Shipping: 1850 cents -> Total: 10390 - 520 + 1850 = 11720 cents
    expect($result->number)->toMatch('/^VV-\d{4}-000001$/')
        ->and($result->paymentStatus)->toBe(PaymentStatus::Pending)
        ->and($result->fulfillmentStatus)->toBe(FulfillmentStatus::Pending)
        ->and($result->subtotal->getMinorAmount()->toInt())->toBe(10390)
        ->and($result->discount->getMinorAmount()->toInt())->toBe(520)
        ->and($result->shipping->getMinorAmount()->toInt())->toBe(1850)
        ->and($result->total->getMinorAmount()->toInt())->toBe(11720);

    /** @var Order $order */
    $order = Order::query()->with(['items', 'adjustments', 'payments', 'statusHistories'])->findOrFail($result->id);

    // ADR 1: sum of allocated line discounts must equal order discount_cents
    $sumLineDiscounts = $order->items->sum(fn ($item): int => $item->discount_cents->getMinorAmount()->toInt());
    $sumLineTotals = $order->items->sum(fn ($item): int => $item->total_cents->getMinorAmount()->toInt());
    expect($sumLineDiscounts)->toBe(520)
        ->and($sumLineTotals)->toBe(10390 - 520);

    // ADR 1: adjustments & payments recorded
    expect($order->adjustments)->toHaveCount(1)
        ->and($order->adjustments->first()?->type)->toBe(AdjustmentType::PixDiscount)
        ->and($order->payments)->toHaveCount(1)
        ->and($order->statusHistories)->toHaveCount(2);

    // ADR 3: changing the catalog product later does NOT alter the order snapshot
    $p1->update([
        'name' => 'KitKat Renomeado',
        'promo_price_cents' => Money::ofMinor(9990, MoneyCast::CURRENCY),
    ]);

    $snapshotItem = $order->items->firstWhere('product_id', $p1->id);
    expect($snapshotItem?->name)->toBe('KitKat Caixa 24un')
        ->and($snapshotItem?->unit_price_cents->getMinorAmount()->toInt())->toBe(7990);

    Event::assertDispatched(OrderPlaced::class, fn (OrderPlaced $e): bool => $e->orderId === $order->id);
});

it('creates a POS order already paid and delivered with zero shipping (ADR 5)', function (): void {
    $product = Product::factory()->create([
        'price_cents' => Money::ofMinor(2500, MoneyCast::CURRENCY),
        'promo_price_cents' => null,
        'is_active' => true,
    ]);

    $result = app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Pos,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::Cash,
        customerName: 'Cliente Balcão',
        items: [new PlaceOrderItemData(productId: $product->id, quantity: 3)],
    ));

    expect($result->paymentStatus)->toBe(PaymentStatus::Paid)
        ->and($result->fulfillmentStatus)->toBe(FulfillmentStatus::Delivered)
        ->and($result->shipping->getMinorAmount()->toInt())->toBe(0)
        ->and($result->total->getMinorAmount()->toInt())->toBe(7500);

    $order = Order::query()->findOrFail($result->id);
    expect($order->paid_at)->not->toBeNull()
        ->and($order->delivered_at)->not->toBeNull();
});

it('applies a valid coupon, increments uses_count and rejects inactive products', function (): void {
    $active = Product::factory()->create([
        'price_cents' => Money::ofMinor(5000, MoneyCast::CURRENCY),
        'promo_price_cents' => null,
        'is_active' => true,
    ]);

    $inactive = Product::factory()->create([
        'price_cents' => Money::ofMinor(1000, MoneyCast::CURRENCY),
        'is_active' => false,
    ]);

    $coupon = Coupon::query()->create([
        'code' => 'VEVEY10',
        'type' => CouponType::Percent,
        'value' => 10,
        'min_subtotal_cents' => Money::ofMinor(2000, MoneyCast::CURRENCY),
        'is_active' => true,
    ]);

    $result = app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Web,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::CreditCard,
        customerName: 'João Silva',
        couponCode: 'vevey10',
        items: [new PlaceOrderItemData(productId: $active->id, quantity: 2)],
    ));

    expect($result->discount->getMinorAmount()->toInt())->toBe(1000)
        ->and($result->total->getMinorAmount()->toInt())->toBe(9000)
        ->and($coupon->refresh()->uses_count)->toBe(1);

    expect(fn () => app(PlaceOrder::class)->handle(new PlaceOrderData(
        channel: OrderChannel::Web,
        deliveryMethod: DeliveryMethod::Pickup,
        paymentMethod: PaymentMethod::CreditCard,
        customerName: 'João Silva',
        items: [new PlaceOrderItemData(productId: $inactive->id, quantity: 1)],
    )))->toThrow(OrderPlacementException::class);
});

it('persists an order via POST /checkout and updates status with history', function (): void {
    $product = Product::factory()->create([
        'sku' => 'DOC-BIS-CX20',
        'name' => 'Bis Caixa 20un',
        'price_cents' => Money::ofMinor(4000, MoneyCast::CURRENCY),
        'promo_price_cents' => null,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('store.checkout.submit'), [
        'customer_name' => 'Carla Mendes',
        'customer_email' => 'carla@vevey.test',
        'customer_phone' => '11988887777',
        'delivery_type' => 'pickup',
        'shipping_code' => 'pickup',
        'payment_method' => 'pix',
        'items' => [
            ['id' => $product->id, 'qty' => 2],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('subtotal_cents', 8000)
        ->assertJsonPath('discount_cents', 400)
        ->assertJsonPath('total_cents', 7600)
        ->assertJsonPath('payment_status', 'pending');

    $orderId = (string) $response->json('order_id');
    $order = Order::query()->findOrFail($orderId);

    app(UpdateOrderStatus::class)->handle(
        order: $order,
        paymentStatus: PaymentStatus::Paid,
        fulfillmentStatus: FulfillmentStatus::Preparing,
        note: 'Pix compensado',
    );

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->fulfillment_status)->toBe(FulfillmentStatus::Preparing)
        ->and($order->paid_at)->not->toBeNull()
        ->and($order->statusHistories()->count())->toBe(4);
});
