<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Coupons\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Vevey\Orders\Enums\CouponType;
use Vevey\Orders\Models\Coupon;
use Vevey\PanelAdmin\Filament\Resources\Products\Tables\MoneyColumn;

final class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('value')
                    ->label('Desconto')
                    ->formatStateUsing(static fn (Coupon $record): string => $record->type === CouponType::Percent
                        ? "{$record->value}%"
                        : 'R$ ' . number_format($record->value / 100, 2, ',', '.')),
                MoneyColumn::make('min_subtotal_cents')
                    ->label('Pedido mínimo'),
                TextColumn::make('uses_count')
                    ->label('Usos')
                    ->formatStateUsing(static fn (Coupon $record): string => $record->max_uses !== null
                        ? "{$record->uses_count} / {$record->max_uses}"
                        : (string) $record->uses_count),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label('Expira em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Sem expiração'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
