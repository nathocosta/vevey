<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Override;
use Vevey\Inventory\Models\StockLevel;
use Vevey\PanelAdmin\Filament\Resources\StockLevels\StockLevelResource;

final class LowStockAlertsWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Alertas de reposição e saldo negativo';

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => StockLevel::query()
                ->with('product')
                ->whereColumn('on_hand', '<=', 'reorder_point')
                ->orderBy('on_hand')
                ->limit(6))
            ->paginated(false)
            ->recordUrl(static fn (): string => StockLevelResource::getUrl('index'))
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->fontFamily('mono'),
                TextColumn::make('product.name')
                    ->label('Produto')
                    ->limit(36),
                TextColumn::make('on_hand')
                    ->label('Físico')
                    ->badge()
                    ->color(static fn (StockLevel $record): string => $record->on_hand < 0 ? 'danger' : 'warning'),
                TextColumn::make('reorder_point')
                    ->label('Mínimo'),
            ])
            ->emptyStateHeading('Estoque saudável')
            ->emptyStateDescription('Nenhum produto está negativo ou abaixo do ponto de reposição.');
    }
}
