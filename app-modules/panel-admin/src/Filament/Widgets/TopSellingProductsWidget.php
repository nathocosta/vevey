<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\OrderItem;

final class TopSellingProductsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Produtos mais vendidos';

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => OrderItem::query()
                ->selectRaw('min(order_items.id::text) as id, order_items.sku, order_items.name, sum(order_items.quantity) as units_sold, sum(order_items.total_cents) as revenue_cents')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', PaymentStatus::Paid->value)
                ->groupBy('order_items.sku', 'order_items.name')
                ->orderByDesc('units_sold')
                ->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->fontFamily('mono'),
                TextColumn::make('name')
                    ->label('Produto')
                    ->limit(36),
                TextColumn::make('units_sold')
                    ->label('Qtd. vendida')
                    ->badge()
                    ->color('info'),
                TextColumn::make('revenue_cents')
                    ->label('Receita')
                    ->formatStateUsing(static fn ($state): string => 'R$ ' . number_format(((int) $state) / 100, 2, ',', '.')),
            ])
            ->emptyStateHeading('Nenhuma venda paga ainda')
            ->emptyStateDescription('Assim que pedidos forem pagos no site ou no PDV, os campeões de venda aparecem aqui.');
    }
}
