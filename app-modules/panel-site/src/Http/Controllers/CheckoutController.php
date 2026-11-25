<?php

declare(strict_types=1);

namespace Vevey\PanelSite\Http\Controllers;

use App\Casts\MoneyCast;
use App\Http\Controllers\Controller;
use App\Services\BrasilApiService;
use App\Services\ShippingCalculator;
use Brick\Money\Money;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\Customers\Actions\FindOrCreateCustomerByContact;
use Vevey\Customers\Enums\IdentitySource;
use Vevey\Customers\Models\Customer;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Orders\Actions\PlaceOrder;
use Vevey\Orders\Actions\UpdateOrderStatus;
use Vevey\Orders\DTOs\OrderItemData;
use Vevey\Orders\DTOs\PlaceOrderData;
use Vevey\Orders\DTOs\PlaceOrderItemData;
use Vevey\Orders\DTOs\ShippingAddressData;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Exceptions\OrderPlacementException;
use Vevey\Orders\Models\Coupon;
use Vevey\Orders\Models\Order;

final class CheckoutController extends Controller
{
    /** @var list<string> */
    private const array BRAZILIAN_STATES = [
        'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO',
        'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI',
        'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
    ];
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

    public function showTrack(): View
    {
        $navCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('store.track', [
            'navCategories' => $navCategories,
            'order' => null,
            'searched' => false,
        ]);
    }

    public function lookupOrder(Request $request): View
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:32'],
            'email' => ['required', 'email', 'max:120'],
        ]);

        $number = mb_strtoupper(mb_trim((string) $validated['order_number']));
        $email = mb_strtolower(mb_trim((string) $validated['email']));

        $order = Order::query()
            ->where('number', $number)
            ->whereRaw('lower(customer_email) = ?', [$email])
            ->with(['items', 'statusHistories'])
            ->first();

        $navCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('store.track', [
            'navCategories' => $navCategories,
            'order' => $order,
            'searched' => true,
            'orderNumberInput' => $number,
            'emailInput' => $email,
        ]);
    }

    public function lookupCep(string $cep, BrasilApiService $brasilApi): JsonResponse
    {
        $address = $brasilApi->lookupCep($cep);

        if ($address === null) {
            return response()->json([
                'message' => 'CEP não encontrado.',
            ], 404);
        }

        return response()
            ->json($address)
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function validateCart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        /** @var list<array{id: string, qty: int}> $requestedItems */
        $requestedItems = $validated['items'];
        $ids = array_values(array_unique(array_column($requestedItems, 'id')));

        $products = Product::query()
            ->active()
            ->whereKey($ids)
            ->with(['categories', 'media'])
            ->get()
            ->keyBy('id');

        $stockByProduct = StockLevel::query()
            ->whereIn('product_id', $ids)
            ->get(['product_id', 'on_hand', 'reserved'])
            ->mapWithKeys(static fn (StockLevel $level): array => [
                $level->product_id => max(0, $level->on_hand - $level->reserved),
            ]);

        $synced = [];
        $warnings = [];

        foreach ($requestedItems as $entry) {
            $product = $products->get($entry['id']);
            if (! $product instanceof Product) {
                $warnings[] = 'Um item do carrinho não está mais disponível e foi removido.';

                continue;
            }

            $available = (int) ($stockByProduct->get($product->id, 0));
            if ($available <= 0) {
                $warnings[] = "{$product->name} está esgotado no momento.";

                continue;
            }

            $clampedQty = min((int) $entry['qty'], $available, 99);
            if ($clampedQty < (int) $entry['qty']) {
                $warnings[] = "Quantidade de {$product->name} ajustada para {$clampedQty} (estoque disponível).";
            }

            $synced[] = [
                'id' => $product->id,
                'name' => $product->name,
                'priceCents' => $product->sale_price->getMinorAmount()->toInt(),
                'image' => StorefrontController::productImageUrl($product, thumb: true) ?? '',
                'url' => route('store.product', ['slug' => $product->slug]),
                'qty' => $clampedQty,
                'available' => $available,
            ];
        }

        return response()->json([
            'items' => $synced,
            'warnings' => $warnings,
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'subtotal_cents' => ['required', 'integer', 'min:0', 'max:100000000'],
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
            'subtotal_cents' => ['nullable', 'integer', 'min:0', 'max:100000000'],
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
        if ($request->filled('state')) {
            $request->merge(['state' => mb_strtoupper(mb_trim((string) $request->input('state')))]);
        }

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
            'state' => ['required_if:delivery_type,delivery', 'nullable', 'string', Rule::in(self::BRAZILIAN_STATES)],
            'items' => ['required', 'array', 'min:1', 'max:50'],
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

        // Bind order ownership to current session for sandbox payment confirmation (prevents IDOR)
        /** @var list<string> $recentOrders */
        $recentOrders = (array) $request->session()->get('recent_checkout_orders', []);
        $recentOrders[] = $order->id;
        $request->session()->put('recent_checkout_orders', array_slice(array_values(array_unique($recentOrders)), -10));

        $totalCents = $order->total->getMinorAmount()->toInt();
        $pixCode = $order->paymentMethod === PaymentMethod::Pix
            ? $this->buildSandboxPixPayload($order->number, $totalCents)
            : null;

        $simulatePayUrl = URL::temporarySignedRoute(
            'store.checkout.simulate-pay',
            now()->addMinutes(30),
            ['orderId' => $order->id],
        );

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
            'total_cents' => $totalCents,
            'pix_code' => $pixCode,
            'simulate_pay_url' => $simulatePayUrl,
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

    /**
     * Simula aprovação de pagamento (Sandbox para demonstração do TCC):
     * confirma pagamento, avança separação e dispara baixa real no ledger de estoque (ADR 2).
     * Protegido por URL assinada temporária (30 min) + validação de posse da sessão/cliente.
     */
    public function simulatePayment(Request $request, string $orderId, UpdateOrderStatus $updater): JsonResponse
    {
        $order = Order::query()->findOrFail($orderId);

        /** @var list<string> $recentOrders */
        $recentOrders = (array) $request->session()->get('recent_checkout_orders', []);
        $authenticatedCustomerId = Auth::guard('customers')->id();
        $ownsOrder = in_array($order->id, $recentOrders, true)
            || (is_string($authenticatedCustomerId) && $order->customer_id === $authenticatedCustomerId);

        if (! $ownsOrder) {
            return response()->json([
                'message' => 'Você não tem permissão para confirmar este pedido.',
            ], 403);
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            return response()->json([
                'order_code' => $order->number,
                'payment_status' => $order->payment_status->value,
                'fulfillment_status' => $order->fulfillment_status->value,
                'message' => 'Pagamento já confirmado.',
            ]);
        }

        if (
            $order->payment_status !== PaymentStatus::Pending
            || $order->fulfillment_status === FulfillmentStatus::Cancelled
        ) {
            return response()->json([
                'message' => 'Este pedido não pode mais ter o pagamento confirmado.',
            ], 409);
        }

        $updated = $updater->handle(
            order: $order,
            paymentStatus: PaymentStatus::Paid,
            fulfillmentStatus: FulfillmentStatus::Preparing,
            note: 'Pagamento aprovado via Sandbox de demonstração (Checkout).',
        );

        return response()->json([
            'order_code' => $updated->number,
            'payment_status' => $updated->payment_status->value,
            'fulfillment_status' => $updated->fulfillment_status->value,
            'message' => 'Pagamento aprovado! Estoque baixado no ledger e pedido enviado para separação.',
        ]);
    }

    private function buildSandboxPixPayload(string $orderNumber, int $totalCents): string
    {
        $amount = number_format($totalCents / 100, 2, '.', '');
        $txid = preg_replace('/[^A-Za-z0-9]/', '', $orderNumber) ?: 'VEVEYDEMO';

        return "00020126580014BR.GOV.BCB.PIX0136pix-sandbox@veveydoces.com.br52040000530398654"
            . str_pad((string) strlen($amount), 2, '0', STR_PAD_LEFT) . $amount
            . "5802BR5911VEVEY DOCES6009SAO PAULO62180514{$txid}6304" . strtoupper(substr(md5($txid . $amount), 0, 4));
    }
}
