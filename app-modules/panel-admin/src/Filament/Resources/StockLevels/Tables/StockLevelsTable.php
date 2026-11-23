<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\StockLevels\Tables;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Vevey\Inventory\Actions\AdjustStock;
use Vevey\Inventory\Actions\ReceiveStock;
use Vevey\Inventory\DTOs\AdjustStockData;
use Vevey\Inventory\DTOs\ReceiveStockData;
use Vevey\Inventory\Models\StockLevel;

final class StockLevelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with('product'))
            ->defaultSort('on_hand', 'asc')
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('on_hand')
                    ->label('Físico')
                    ->badge()
                    ->color(static fn (StockLevel $record): string => match (true) {
                        $record->on_hand < 0 => 'danger',
                        $record->on_hand <= $record->reorder_point => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(static fn (int $state): string => $state < 0 ? "{$state} (negativo!)" : (string) $state)
                    ->sortable(),
                TextColumn::make('reserved')
                    ->label('Reservado')
                    ->sortable(),
                TextColumn::make('available')
                    ->label('Disponível')
                    ->weight('semibold')
                    ->getStateUsing(static fn (StockLevel $record): int => $record->on_hand - $record->reserved),
                TextColumn::make('reorder_point')
                    ->label('Estoque mínimo')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('negative')
                    ->label('Estoque negativo (alerta PDV)')
                    ->query(static fn (Builder $query): Builder => $query->where('on_hand', '<', 0)),
                Filter::make('low')
                    ->label('Abaixo do mínimo')
                    ->query(static fn (Builder $query): Builder => $query->whereColumn('on_hand', '<=', 'reorder_point')),
            ])
            ->recordActions([
                Action::make('receive')
                    ->label('Lançar entrada')
                    ->icon(Heroicon::OutlinedPlusCircle)
                    ->color('success')
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Quantidade recebida')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('reorder_point')
                            ->label('Estoque mínimo (alerta)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(static fn (StockLevel $record): int => $record->reorder_point),
                        Textarea::make('note')
                            ->label('Observação / Nota fiscal')
                            ->rows(2)
                            ->placeholder('Ex.: Reposição fornecedor NF 1234'),
                    ])
                    ->action(function (StockLevel $record, array $data, ReceiveStock $receiveStock): void {
                        $receiveStock->handle(new ReceiveStockData(
                            productId: $record->product_id,
                            quantity: (int) $data['quantity'],
                            note: isset($data['note']) && $data['note'] !== '' ? (string) $data['note'] : null,
                            userId: Auth::id() ? (string) Auth::id() : null,
                            reorderPoint: isset($data['reorder_point']) ? (int) $data['reorder_point'] : null,
                        ));

                        Notification::make()
                            ->title('Entrada registrada no ledger')
                            ->success()
                            ->send();
                    }),

                Action::make('adjust')
                    ->label('Contagem / Ajustar')
                    ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                    ->color('warning')
                    ->schema([
                        TextInput::make('counted_qty')
                            ->label('Quantidade contada na prateleira')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(static fn (StockLevel $record): int => max(0, $record->on_hand))
                            ->helperText(static fn (StockLevel $record): string => "Saldo atual no sistema: {$record->on_hand}. A diferença será lançada como ajuste no ledger.")
                            ->required(),
                        TextInput::make('reorder_point')
                            ->label('Estoque mínimo (alerta)')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(static fn (StockLevel $record): int => $record->reorder_point),
                        Textarea::make('note')
                            ->label('Motivo do ajuste')
                            ->rows(2)
                            ->required()
                            ->placeholder('Ex.: Contagem física mensal, quebra ou avaria'),
                    ])
                    ->action(function (StockLevel $record, array $data, AdjustStock $adjustStock): void {
                        $adjustStock->handle(new AdjustStockData(
                            productId: $record->product_id,
                            countedQty: (int) $data['counted_qty'],
                            note: (string) $data['note'],
                            userId: Auth::id() ? (string) Auth::id() : null,
                            reorderPoint: isset($data['reorder_point']) ? (int) $data['reorder_point'] : null,
                        ));

                        Notification::make()
                            ->title('Contagem registrada no ledger')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
