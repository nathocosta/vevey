<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Orders\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\PanelAdmin\Filament\Resources\Products\Tables\MoneyColumn;

final class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->fontFamily('mono')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->description(fn ($record): ?string => $record->customer_phone ?: $record->customer_email)
                    ->searchable(['customer_name', 'customer_phone', 'customer_email']),
                TextColumn::make('payment_status')
                    ->label('Pagamento')
                    ->badge()
                    ->sortable(),
                TextColumn::make('fulfillment_status')
                    ->label('Entrega')
                    ->badge()
                    ->sortable(),
                MoneyColumn::make('total_cents')
                    ->label('Total')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Canal')
                    ->options(OrderChannel::class),
                SelectFilter::make('payment_status')
                    ->label('Pagamento')
                    ->options(PaymentStatus::class),
                SelectFilter::make('fulfillment_status')
                    ->label('Entrega')
                    ->options(FulfillmentStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
