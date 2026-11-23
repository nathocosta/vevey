<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Casts\MoneyCast;
use App\Services\ShippingCalculator;
use Brick\Money\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\Customers\Actions\FindOrCreateCustomerByContact;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Models\Customer;
use Vevey\Orders\Actions\PlaceOrder;
use Vevey\Orders\DTOs\OrderItemData;
use Vevey\Orders\DTOs\PlaceOrderData;
use Vevey\Orders\DTOs\PlaceOrderItemData;
use Vevey\Orders\DTOs\ShippingAddressData;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Exceptions\OrderPlacementException;
use Vevey\Orders\Models\Coupon;

final class CheckoutController extends Controller
{
    public function show(): View
    {
        $navCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with([
                'children' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('position'),
            ])
            ->orderBy('position')
            ->get();

        /** @var Customer|null $customer */
        $customer = Auth::guard('customers')->user();
        $customer?->load(['addresses', 'activeIdentities']);

        return view('store.checkout', [
            'navCategories' => $navCategories,
            'customer' => $customer,
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'subtotal_cents' => ['required', 'integer', 'min:0'],
        ]);

        $code = mb_strtoupper(mb_trim((string) $validated['code']));
        $subtotal = Money::ofMinor((int) $validated['subtotal_cents'], MoneyCast::CURRENCY);

        $coupon = Coupon::query()->where('code', $code)->first();

        if (! $coupon instanceof Coupon || ! $coupon->isValidFor($subtotal)) {
            return response()->json([
                'message' => 'Cupom inválido, expirado ou abaixo do valor mínimo.',
            ], 422);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'code' => $coupon->code,
            'label' => "Cupom {$coupon->code}",
            'discount_cents' => $discount->getMinorAmount()->toInt(),
        ]);
    }

    public function quoteShipping(Request $request, ShippingCalculator $calculator): JsonResponse
    {
        $validated = $request->validate([
            'cep' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'subtotal_cents' => ['nullable', 'integer', 'min:0'],
            'weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:30'],
        ]);

        $quote = $calculator->quote(
            rawCep: (string) $validated['cep'],
            subtotalCents: (int) ($validated['subtotal_cents'] ?? 0),
            weightKg: (float) ($validated['weight_kg'] ?? 1.0),
        );

        return response()->json($quote);
    }

    public function submit(
        Request $request,
        ShippingCalculator $calculator,
        PlaceOrder $placeOrder,
        FindOrCreateCustomerByContact $findOrCreateCustomer,
    ): JsonResponse {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:3', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:10', 'max:20'],
            'delivery_type' => ['required', 'in:pickup,delivery'],
            'shipping_code' => ['required', 'string', 'max:40'],
            'payment_method' => ['required', 'in:pix,credit_card'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'cep' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:160'],
            'number' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:120'],
            'neighborhood' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:100'],
            'city' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:100'],
            'state' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'size:2'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        /** @var list<array{id: string, qty: int}> $requestedItems */
        $requestedItems = $validated['items'];

        // Pre-compute shipping quote on the server using current active product prices
        $shippingCents = 0;
        $shippingLabel = 'Retirada na loja Vevey (mesmo dia)';
        $shippingAddress = null;

        if ($validated['delivery_type'] === 'delivery') {
            $productIds = array_column($requestedItems, 'id');
            $products = Product::query()->active()->whereKey($productIds)->get()->keyBy('id');
            $estimatedSubtotalCents = 0;

            foreach ($requestedItems as $item) {
                $product = $products->get($item['id']);
                if ($product instanceof Product) {
                    $estimatedSubtotalCents += $product->sale_price->getMinorAmount()->toInt() * (int) $item['qty'];
                }
            }

            $quote = $calculator->quote((string) $validated['cep'], $estimatedSubtotalCents);
            $matched = collect($quote['options'])->firstWhere('code', $validated['shipping_code'])
                ?? collect($quote['options'])->firstWhere('type', 'delivery');

            if ($matched !== null) {
                $shippingCents = (int) $matched['price_cents'];
                $shippingLabel = $matched['name'] . ' — ' . $matched['delivery_label'];
            }

            $shippingAddress = new ShippingAddressData(
                cep: (string) $validated['cep'],
                street: (string) $validated['street'],
                number: (string) $validated['number'],
                neighborhood: (string) $validated['neighborhood'],
                city: (string) $validated['city'],
                state: mb_strtoupper((string) $validated['state']),
                complement: isset($validated['complement']) ? (string) $validated['complement'] : null,
            );
        }

        $customerId = Auth::guard('customers')->id();
        if (! is_string($customerId)) {
            $customerData = $findOrCreateCustomer->handle(
                name: (string) $validated['customer_name'],
                email: (string) $validated['customer_email'],
                phone: (string) $validated['customer_phone'],
                source: IdentitySource::Web,
            );
            $customerId = $customerData->id;
        }

        try {
            $order = $placeOrder->handle(new PlaceOrderData(
                channel: OrderChannel::Web,
                deliveryMethod: DeliveryMethod::from((string) $validated['delivery_type']),
                paymentMethod: PaymentMethod::from((string) $validated['payment_method']),
                customerName: (string) $validated['customer_name'],
                customerEmail: (string) $validated['customer_email'],
                customerPhone: (string) $validated['customer_phone'],
                customerId: $customerId,
                shippingMethod: $shippingLabel,
                shippingCost: Money::ofMinor($shippingCents, MoneyCast::CURRENCY),
                shippingAddress: $shippingAddress,
                couponCode: isset($validated['coupon_code']) ? (string) $validated['coupon_code'] : null,
                items: array_map(
                    static fn (array $item): PlaceOrderItemData => new PlaceOrderItemData(
                        productId: $item['id'],
                        quantity: (int) $item['qty'],
                    ),
                    $requestedItems,
                ),
            ));
        } catch (OrderPlacementException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'order_id' => $order->id,
            'order_code' => $order->number,
            'customer_name' => $order->customerName,
            'delivery_type' => $order->deliveryMethod->value,
            'shipping_label' => $order->shippingMethod ?? $shippingLabel,
            'payment_method' => $order->paymentMethod->value,
            'payment_status' => $order->paymentStatus->value,
            'fulfillment_status' => $order->fulfillmentStatus->value,
            'subtotal_cents' => $order->subtotal->getMinorAmount()->toInt(),
            'discount_cents' => $order->discount->getMinorAmount()->toInt(),
            'shipping_cents' => $order->shipping->getMinorAmount()->toInt(),
            'total_cents' => $order->total->getMinorAmount()->toInt(),
            'items' => array_map(
                static fn (OrderItemData $item): array => [
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'qty' => $item->quantity,
                    'unit_cents' => $item->unitPrice->getMinorAmount()->toInt(),
                    'discount_cents' => $item->discount->getMinorAmount()->toInt(),
                    'line_cents' => $item->total->getMinorAmount()->toInt(),
                ],
                $order->items,
            ),
        ], 201);
    }
}
