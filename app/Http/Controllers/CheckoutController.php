<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ShippingCalculator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;

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

        return view('store.checkout', [
            'navCategories' => $navCategories,
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

    public function submit(Request $request, ShippingCalculator $calculator): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:3', 'max:120'],
            'customer_email' => ['required', 'email', 'max:120'],
            'customer_phone' => ['required', 'string', 'min:10', 'max:20'],
            'delivery_type' => ['required', 'in:pickup,delivery'],
            'shipping_code' => ['required', 'string', 'max:40'],
            'payment_method' => ['required', 'in:pix,credit_card'],
            'cep' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'street' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:160'],
            'number' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:30'],
            'neighborhood' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:100'],
            'city' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'max:100'],
            'state' => ['required_if:delivery_type,delivery', 'nullable', 'string', 'size:2'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        /** @var list<array{id: string, qty: int}> $requestedItems */
        $requestedItems = $validated['items'];
        $productIds = array_column($requestedItems, 'id');

        $products = Product::query()
            ->active()
            ->whereKey($productIds)
            ->get()
            ->keyBy('id');

        $subtotalCents = 0;
        $resolvedItems = [];

        foreach ($requestedItems as $item) {
            $product = $products->get($item['id']);

            if (! $product instanceof Product) {
                return response()->json([
                    'message' => 'Um ou mais itens do carrinho não estão mais disponíveis.',
                ], 422);
            }

            $unitCents = $product->sale_price->getMinorAmount()->toInt();
            $lineCents = $unitCents * (int) $item['qty'];
            $subtotalCents += $lineCents;

            $resolvedItems[] = [
                'name' => $product->name,
                'sku' => $product->sku,
                'qty' => (int) $item['qty'],
                'unit_cents' => $unitCents,
                'line_cents' => $lineCents,
            ];
        }

        $shippingCents = 0;
        $shippingLabel = 'Retirada na loja Vevey (mesmo dia)';

        if ($validated['delivery_type'] === 'delivery') {
            $quote = $calculator->quote((string) $validated['cep'], $subtotalCents);
            $matched = collect($quote['options'])->firstWhere('code', $validated['shipping_code'])
                ?? collect($quote['options'])->firstWhere('type', 'delivery');

            if ($matched !== null) {
                $shippingCents = (int) $matched['price_cents'];
                $shippingLabel = $matched['name'] . ' — ' . $matched['delivery_label'];
            }
        }

        $pixDiscountCents = $validated['payment_method'] === 'pix'
            ? (int) round($subtotalCents * 0.05)
            : 0;

        $totalCents = max(0, $subtotalCents - $pixDiscountCents + $shippingCents);
        $orderCode = 'VEV-' . now()->format('Ymd') . '-' . mb_strtoupper(Str::random(4));

        return response()->json([
            'order_code' => $orderCode,
            'customer_name' => $validated['customer_name'],
            'delivery_type' => $validated['delivery_type'],
            'shipping_label' => $shippingLabel,
            'payment_method' => $validated['payment_method'],
            'subtotal_cents' => $subtotalCents,
            'discount_cents' => $pixDiscountCents,
            'shipping_cents' => $shippingCents,
            'total_cents' => $totalCents,
            'items' => $resolvedItems,
        ], 201);
    }
}
