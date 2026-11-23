<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;
use Vevey\PanelAdmin\Filament\Resources\Orders\OrderResource;
use Vevey\PanelAdmin\Filament\Resources\StockLevels\StockLevelResource;

final class SalesOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    #[Override]
    protected function getStats(): array
    {
        $paidQuery = Order::query()->where('payment_status', PaymentStatus::Paid);
        $paidCount = (clone $paidQuery)->count();
        $revenueCents = (int) (clone $paidQuery)->sum('total_cents');
        $avgTicketCents = $paidCount > 0 ? (int) round($revenueCents / $paidCount) : 0;

        $openOrdersCount = Order::query()
            ->whereIn('fulfillment_status', [
                FulfillmentStatus::Pending->value,
                FulfillmentStatus::Preparing->value,
                FulfillmentStatus::Ready->value,
            ])
            ->count();

        $negativeStockCount = StockLevel::query()->where('on_hand', '<', 0)->count();
        $lowStockCount = StockLevel::query()->whereColumn('on_hand', '<=', 'reorder_point')->count();

        return [
            Stat::make('Faturamento (pedidos pagos)', self::formatBrl($revenueCents))
                ->description("{$paidCount} pedido(s) pago(s)")
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Ticket médio', self::formatBrl($avgTicketCents))
                ->description('Média por pedido pago')
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color('info'),

            Stat::make('Pedidos em aberto', (string) $openOrdersCount)
                ->description('Recebidos, em separação ou prontos')
                ->descriptionIcon(Heroicon::OutlinedClipboardDocumentList)
                ->color($openOrdersCount > 0 ? 'warning' : 'gray')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Alertas de estoque', (string) $lowStockCount)
                ->description($negativeStockCount > 0
                    ? "{$negativeStockCount} negativo(s) no balcão!"
                    : ($lowStockCount > 0 ? 'Abaixo do estoque mínimo' : 'Todos os saldos saudáveis'))
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($negativeStockCount > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success'))
                ->url(StockLevelResource::getUrl('index')),
        ];
    }

    private static function formatBrl(int $cents): string
    {
        return 'R$ ' . number_format($cents / 100, 2, ',', '.');
    }
}
