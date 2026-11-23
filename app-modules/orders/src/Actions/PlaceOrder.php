<?php

declare(strict_types=1);

namespace Vevey\Orders\Actions;

use App\Casts\MoneyCast;
use Brick\Money\AllocationMode;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;
use Vevey\Catalog\Actions\FindProductsForSale;
use Vevey\Catalog\DTOs\ProductAttributeData;
use Vevey\Catalog\DTOs\ProductData;
use Vevey\Orders\DTOs\OrderData;
use Vevey\Orders\DTOs\PlaceOrderData;
use Vevey\Orders\Enums\AdjustmentType;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Events\OrderPlaced;
use Vevey\Orders\Exceptions\OrderPlacementException;
use Vevey\Orders\Models\Coupon;
use Vevey\Orders\Models\Order;
use Vevey\Orders\Models\OrderAdjustment;
use Vevey\Orders\Models\OrderItem;
use Vevey\Orders\Models\OrderStatusHistory;
use Vevey\Orders\Models\Payment;

/**
 * Single entry point for creating orders across all channels (web, pos, whatsapp — ADR 4 & 5).
 * Reads product snapshots via Catalog's FindProductsForSale action (ADR 3 & 8).
 */
final class PlaceOrder
{
    private const float PIX_DISCOUNT_RATE = 0.05;

    public function __construct(
        private readonly FindProductsForSale $findProductsForSale,
    ) {}

    public function handle(PlaceOrderData $data): OrderData
    {
        if ($data->items === []) {
            throw new OrderPlacementException('O pedido precisa ter pelo menos um item.');
        }

        // Merge duplicate product lines safely
        $quantitiesByProduct = [];
        foreach ($data->items as $item) {
            if ($item->quantity <= 0) {
                throw new OrderPlacementException('A quantidade de cada item deve ser maior que zero.');
            }

            $quantitiesByProduct[$item->productId] = ($quantitiesByProduct[$item->productId] ?? 0) + $item->quantity;
        }

        $productIds = array_keys($quantitiesByProduct);
        $products = $this->findProductsForSale->handle($productIds);

        if (count($products) !== count($productIds)) {
            throw new OrderPlacementException('Um ou mais itens do carrinho não estão mais disponíveis.');
        }

        return DB::transaction(function () use ($data, $quantitiesByProduct, $products): OrderData {
            $zero = Money::ofMinor(0, MoneyCast::CURRENCY);

            /** @var list<array{product: ProductData, quantity: int, gross: Money}> $lines */
            $lines = [];
            $subtotal = $zero;

            foreach ($quantitiesByProduct as $productId => $quantity) {
                $product = $products[$productId];
                $gross = $product->salePrice->multipliedBy($quantity);
                $subtotal = $subtotal->plus($gross);

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'gross' => $gross,
                ];
            }

            /** @var list<array{type: AdjustmentType, label: string, amount: Money, metadata: array<string, mixed>|null}> $adjustments */
            $adjustments = [];
            $coupon = null;
            $totalDiscount = $zero;

            if ($data->couponCode !== null && mb_trim($data->couponCode) !== '') {
                $normalizedCode = mb_strtoupper(mb_trim($data->couponCode));
                $coupon = Coupon::query()
                    ->where('code', $normalizedCode)
                    ->lockForUpdate()
                    ->first();

                if (! $coupon instanceof Coupon || ! $coupon->isValidFor($subtotal)) {
                    throw new OrderPlacementException('Cupom inválido, expirado ou abaixo do valor mínimo.');
                }

                $couponDiscount = $coupon->calculateDiscount($subtotal);
                if ($couponDiscount->isPositive()) {
                    $totalDiscount = $totalDiscount->plus($couponDiscount);
                    $adjustments[] = [
                        'type' => AdjustmentType::Coupon,
                        'label' => "Cupom {$coupon->code}",
                        'amount' => $couponDiscount,
                        'metadata' => ['coupon_id' => $coupon->id, 'code' => $coupon->code],
                    ];
                }
            }

            // 5% Pix discount on remaining subtotal for remote orders (web/whatsapp)
            if ($data->paymentMethod === PaymentMethod::Pix && $data->channel !== OrderChannel::Pos) {
                $baseForPix = $subtotal->minus($totalDiscount);
                if ($baseForPix->isPositive()) {
                    $pixCents = (int) round($baseForPix->getMinorAmount()->toInt() * self::PIX_DISCOUNT_RATE);
                    if ($pixCents > 0) {
                        $pixDiscount = Money::ofMinor($pixCents, MoneyCast::CURRENCY);
                        $totalDiscount = $totalDiscount->plus($pixDiscount);
                        $adjustments[] = [
                            'type' => AdjustmentType::PixDiscount,
                            'label' => 'Desconto Pix (5% à vista)',
                            'amount' => $pixDiscount,
                            'metadata' => ['rate' => self::PIX_DISCOUNT_RATE],
                        ];
                    }
                }
            }

            // Cap discount so it never exceeds subtotal
            if ($totalDiscount->isGreaterThan($subtotal)) {
                $totalDiscount = $subtotal;
            }

            // POS and Pickup never have shipping cost (ADR 4 & 5)
            $isDelivery = $data->deliveryMethod === DeliveryMethod::Delivery && $data->channel !== OrderChannel::Pos;
            $shipping = $isDelivery && $data->shippingCost instanceof Money
                ? $data->shippingCost
                : $zero;

            $total = $subtotal->minus($totalDiscount)->plus($shipping);

            $now = now();
            $isPos = $data->channel === OrderChannel::Pos;

            $paymentStatus = $isPos ? PaymentStatus::Paid : PaymentStatus::Pending;
            $fulfillmentStatus = $isPos ? FulfillmentStatus::Delivered : FulfillmentStatus::Pending;

            $order = Order::query()->create([
                'number' => $this->nextOrderNumber(),
                'channel' => $data->channel,
                'payment_status' => $paymentStatus,
                'fulfillment_status' => $fulfillmentStatus,
                'delivery_method' => $isPos ? DeliveryMethod::Pickup : $data->deliveryMethod,
                'customer_id' => $data->customerId,
                'seller_id' => $data->sellerId,
                'coupon_id' => $coupon?->id,
                'customer_name' => $data->customerName,
                'customer_email' => $data->customerEmail,
                'customer_phone' => $data->customerPhone,
                'shipping_method' => $isDelivery ? $data->shippingMethod : ($isPos ? 'Balcão (PDV)' : ($data->shippingMethod ?? 'Retirada na loja')),
                'shipping_cep' => $isDelivery ? $data->shippingAddress?->cep : null,
                'shipping_street' => $isDelivery ? $data->shippingAddress?->street : null,
                'shipping_number' => $isDelivery ? $data->shippingAddress?->number : null,
                'shipping_complement' => $isDelivery ? $data->shippingAddress?->complement : null,
                'shipping_neighborhood' => $isDelivery ? $data->shippingAddress?->neighborhood : null,
                'shipping_city' => $isDelivery ? $data->shippingAddress?->city : null,
                'shipping_state' => $isDelivery ? $data->shippingAddress?->state : null,
                'subtotal_cents' => $subtotal,
                'discount_cents' => $totalDiscount,
                'shipping_cents' => $shipping,
                'total_cents' => $total,
                'notes' => $data->notes,
                'paid_at' => $isPos ? $now : null,
                'delivered_at' => $isPos ? $now : null,
            ]);

            if ($coupon instanceof Coupon) {
                $coupon->increment('uses_count');
            }

            // ADR 1: allocate discount proportionally across order lines using Money::allocate()
            $weights = array_map(
                static fn (array $line): int => $line['gross']->getMinorAmount()->toInt(),
                $lines,
            );
            $allocatedDiscounts = $totalDiscount->isPositive()
                ? $totalDiscount->allocate($weights, AllocationMode::FloorToLargestRemainder)
                : array_fill(0, count($lines), $zero);

            foreach ($lines as $index => $line) {
                $product = $line['product'];
                $lineDiscount = $allocatedDiscounts[$index];
                $lineTotal = $line['gross']->minus($lineDiscount);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'unit_price_cents' => $product->salePrice,
                    'quantity' => $line['quantity'],
                    'discount_cents' => $lineDiscount,
                    'total_cents' => $lineTotal,
                    'product_snapshot' => [
                        'barcode' => $product->barcode,
                        'description' => $product->description,
                        'regular_price_cents' => $product->price->getMinorAmount()->toInt(),
                        'promo_price_cents' => $product->promoPrice?->getMinorAmount()->toInt(),
                        'thumbnail_url' => $product->thumbnailUrl,
                        'attributes' => array_map(
                            static fn (ProductAttributeData $attr): array => [
                                'attribute' => $attr->attribute,
                                'value' => $attr->value,
                            ],
                            $product->attributes,
                        ),
                    ],
                ]);
            }

            foreach ($adjustments as $adjustment) {
                OrderAdjustment::query()->create([
                    'order_id' => $order->id,
                    'type' => $adjustment['type'],
                    'label' => $adjustment['label'],
                    'amount_cents' => $adjustment['amount'],
                    'metadata' => $adjustment['metadata'],
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'method' => $data->paymentMethod,
                'provider' => $isPos ? 'pos' : 'manual',
                'amount_cents' => $total,
                'status' => $paymentStatus,
                'expires_at' => ! $isPos && $data->paymentMethod === PaymentMethod::Pix ? $now->copy()->addMinutes(30) : null,
                'paid_at' => $isPos ? $now : null,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'field' => 'payment_status',
                'from_status' => null,
                'to_status' => $paymentStatus->value,
                'user_id' => $data->sellerId,
                'note' => $isPos ? 'Venda registrada no balcão (PDV).' : 'Pedido criado pelo checkout.',
                'created_at' => $now,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'field' => 'fulfillment_status',
                'from_status' => null,
                'to_status' => $fulfillmentStatus->value,
                'user_id' => $data->sellerId,
                'note' => $isPos ? 'Entregue no balcão.' : 'Aguardando separação.',
                'created_at' => $now,
            ]);

            $order->load('items');

            OrderPlaced::dispatch(
                orderId: $order->id,
                orderNumber: $order->number,
                channel: $order->channel,
                totalCents: $order->total_cents->getMinorAmount()->toInt(),
            );

            return OrderData::fromModel($order, $data->paymentMethod);
        });
    }

    private function nextOrderNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "VV-{$year}-";

        $lastNumber = Order::query()
            ->where('number', 'like', "{$prefix}%")
            ->orderByDesc('number')
            ->lockForUpdate()
            ->value('number');

        $nextSeq = 1;
        if (is_string($lastNumber)) {
            $suffix = mb_substr($lastNumber, mb_strlen($prefix));
            if (ctype_digit($suffix)) {
                $nextSeq = (int) $suffix + 1;
            }
        }

        return $prefix . mb_str_pad((string) $nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
