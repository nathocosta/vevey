<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Override;
use Vevey\Catalog\Models\Product;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Orders\Actions\PlaceOrder;
use Vevey\Orders\DTOs\PlaceOrderData;
use Vevey\Orders\DTOs\PlaceOrderItemData;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentMethod;
use Vevey\Orders\Exceptions\OrderPlacementException;
use Vevey\PanelAdmin\Filament\Resources\Orders\OrderResource;

/**
 * ADR 5: Counter sales (PDV) are recorded as `pos` orders (never manual stock edits).
 * Born paid + delivered, zero shipping, allows negative stock with a visual alert.
 */
final class PosSale extends Page
{
    protected static ?string $slug = 'pdv';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 5;

    protected string $view = 'panel-admin::filament.pages.pos-sale';

    public string $search = '';

    public string $customerName = 'Cliente Balcão';

    public string $customerPhone = '';

    public string $paymentMethod = 'cash';

    public string $notes = '';

    /** @var array<string, array{id: string, sku: string, name: string, unit_cents: int, qty: int}> */
    public array $cart = [];

    public ?string $lastOrderNumber = null;

    public ?string $lastOrderUrl = null;

    #[Override]
    public static function getNavigationLabel(): string
    {
        return 'PDV Balcão';
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::orders.navigation_group');
    }

    #[Override]
    public function getTitle(): string
    {
        return 'PDV Balcão — Registrar venda presencial';
    }

    /**
     * @return list<array{id: string, sku: string, name: string, price_cents: int, on_hand: int}>
     */
    public function getProductsProperty(): array
    {
        $query = Product::query()->active()->orderBy('name');

        if (mb_trim($this->search) !== '') {
            $query->search($this->search);
        }

        $products = $query->limit(24)->get();
        $levels = StockLevel::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->pluck('on_hand', 'product_id');

        return $products->map(static fn (Product $product): array => [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price_cents' => $product->sale_price->getMinorAmount()->toInt(),
            'on_hand' => (int) ($levels->get($product->id) ?? 0),
        ])->values()->all();
    }

    public function getSubtotalCentsProperty(): int
    {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['unit_cents'] * $item['qty'];
        }

        return $total;
    }

    public function addToCart(string $productId): void
    {
        $product = Product::query()->active()->find($productId);
        if (! $product instanceof Product) {
            return;
        }

        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['qty']++;

            return;
        }

        $this->cart[$product->id] = [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'unit_cents' => $product->sale_price->getMinorAmount()->toInt(),
            'qty' => 1,
        ];
    }

    public function updateQty(string $productId, int $delta): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $next = $this->cart[$productId]['qty'] + $delta;
        if ($next <= 0) {
            unset($this->cart[$productId]);

            return;
        }

        $this->cart[$productId]['qty'] = $next;
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function submitSale(PlaceOrder $placeOrder): void
    {
        if ($this->cart === []) {
            Notification::make()
                ->title('Adicione pelo menos um produto ao balcão')
                ->warning()
                ->send();

            return;
        }

        $items = array_values(array_map(
            static fn (array $item): PlaceOrderItemData => new PlaceOrderItemData(
                productId: $item['id'],
                quantity: $item['qty'],
            ),
            $this->cart,
        ));

        try {
            $order = $placeOrder->handle(new PlaceOrderData(
                channel: OrderChannel::Pos,
                deliveryMethod: DeliveryMethod::Pickup,
                paymentMethod: PaymentMethod::from($this->paymentMethod),
                customerName: mb_trim($this->customerName) !== '' ? mb_trim($this->customerName) : 'Cliente Balcão',
                customerPhone: mb_trim($this->customerPhone) !== '' ? mb_trim($this->customerPhone) : null,
                sellerId: Auth::id() ? (string) Auth::id() : null,
                notes: mb_trim($this->notes) !== '' ? mb_trim($this->notes) : null,
                items: $items,
            ));
        } catch (OrderPlacementException $exception) {
            Notification::make()
                ->title('Não foi possível concluir a venda')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $negativeCount = StockLevel::query()
            ->whereIn('product_id', array_keys($this->cart))
            ->where('on_hand', '<', 0)
            ->count();

        $this->lastOrderNumber = $order->number;
        $this->lastOrderUrl = OrderResource::getUrl('view', ['record' => $order->id]);
        $this->cart = [];
        $this->customerName = 'Cliente Balcão';
        $this->customerPhone = '';
        $this->notes = '';

        $notification = Notification::make()
            ->title("Venda {$order->number} registrada!")
            ->body($negativeCount > 0
                ? "Venda concluída com sucesso. Atenção: {$negativeCount} item(ns) ficaram com estoque negativo — lance a entrada ou faça contagem."
                : 'Pedido registrado como pago e entregue, com baixa automática no ledger de estoque.');

        if ($negativeCount > 0) {
            $notification->warning()->send();
        } else {
            $notification->success()->send();
        }
    }
}
