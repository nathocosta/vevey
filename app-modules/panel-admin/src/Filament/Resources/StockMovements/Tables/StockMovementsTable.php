<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\StockMovements\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Vevey\Inventory\Enums\StockMovementReason;

final class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with(['product', 'user']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data / Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->badge(),
                TextColumn::make('qty')
                    ->label('Qtd.')
                    ->badge()
                    ->color(static fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->formatStateUsing(static fn (int $state): string => $state > 0 ? "+{$state}" : (string) $state),
                TextColumn::make('balance_after')
                    ->label('Saldo após')
                    ->weight('semibold'),
                TextColumn::make('user.name')
                    ->label('Responsável')
                    ->placeholder('Sistema'),
                TextColumn::make('note')
                    ->label('Observação')
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('reason')
                    ->label('Motivo')
                    ->options(StockMovementReason::class),
            ]);
    }
}
