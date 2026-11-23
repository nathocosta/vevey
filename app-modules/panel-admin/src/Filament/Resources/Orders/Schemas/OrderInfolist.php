<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Orders\Schemas;

use Brick\Money\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Resumo do pedido')
                ->columns(4)
                ->schema([
                    TextEntry::make('number')->label('Número')->fontFamily('mono')->weight('bold'),
                    TextEntry::make('channel')->label('Canal')->badge(),
                    TextEntry::make('payment_status')->label('Pagamento')->badge(),
                    TextEntry::make('fulfillment_status')->label('Entrega')->badge(),
                    TextEntry::make('delivery_method')->label('Modalidade')->badge(),
                    TextEntry::make('shipping_method')->label('Frete / Retirada')->placeholder('—'),
                    TextEntry::make('tracking_code')->label('Rastreio')->placeholder('—'),
                    TextEntry::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i'),
                ]),

            Section::make('Cliente e endereço')
                ->columns(3)
                ->schema([
                    TextEntry::make('customer_name')->label('Nome'),
                    TextEntry::make('customer_email')->label('E-mail')->placeholder('—'),
                    TextEntry::make('customer_phone')->label('Telefone')->placeholder('—'),
                    TextEntry::make('shipping_street')
                        ->label('Endereço de entrega')
                        ->columnSpanFull()
                        ->formatStateUsing(function ($state, $record): string {
                            if ($record->shipping_street === null) {
                                return 'Retirada na loja (sem endereço de entrega)';
                            }

                            $parts = array_filter([
                                trim("{$record->shipping_street}, {$record->shipping_number}"),
                                $record->shipping_complement,
                                $record->shipping_neighborhood,
                                trim("{$record->shipping_city}/{$record->shipping_state}"),
                                $record->shipping_cep ? "CEP {$record->shipping_cep}" : null,
                            ]);

                            return implode(' · ', $parts);
                        }),
                ]),

            Section::make('Totais (snapshot histórico)')
                ->columns(4)
                ->schema([
                    TextEntry::make('subtotal_cents')
                        ->label('Subtotal')
                        ->formatStateUsing(static fn ($state): string => self::formatMoney($state)),
                    TextEntry::make('discount_cents')
                        ->label('Descontos')
                        ->formatStateUsing(static fn ($state): string => self::formatMoney($state)),
                    TextEntry::make('shipping_cents')
                        ->label('Frete')
                        ->formatStateUsing(static fn ($state): string => self::formatMoney($state)),
                    TextEntry::make('total_cents')
                        ->label('Total')
                        ->weight('bold')
                        ->formatStateUsing(static fn ($state): string => self::formatMoney($state)),
                ]),

            Section::make('Itens do pedido')
                ->schema([
                    RepeatableEntry::make('items')
                        ->label('')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('sku')->label('SKU')->fontFamily('mono'),
                            TextEntry::make('name')->label('Produto'),
                            TextEntry::make('quantity')->label('Qtd.'),
                            TextEntry::make('unit_price_cents')
                                ->label('Unitário')
                                ->formatStateUsing(static fn ($state): string => self::formatMoney($state)),
                            TextEntry::make('total_cents')
                                ->label('Total linha')
                                ->weight('semibold')
                                ->formatStateUsing(static fn ($state): string => self::formatMoney($state)),
                        ]),
                ]),

            Section::make('Ajustes e descontos')
                ->schema([
                    RepeatableEntry::make('adjustments')
                        ->label('')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('type')->label('Tipo')->badge(),
                            TextEntry::make('label')->label('Descrição'),
                            TextEntry::make('amount_cents')
                                ->label('Valor')
                                ->formatStateUsing(static fn ($state): string => '- ' . self::formatMoney($state)),
                        ]),
                ]),

            Section::make('Histórico de status')
                ->schema([
                    RepeatableEntry::make('statusHistories')
                        ->label('')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('created_at')->label('Quando')->dateTime('d/m/Y H:i'),
                            TextEntry::make('field')->label('Campo'),
                            TextEntry::make('from_status')->label('De')->placeholder('—'),
                            TextEntry::make('to_status')->label('Para')->badge(),
                            TextEntry::make('note')->label('Observação')->placeholder('—'),
                        ]),
                ]),
        ]);
    }

    private static function formatMoney(mixed $value): string
    {
        if ($value instanceof Money) {
            $cents = $value->getMinorAmount()->toInt();

            return 'R$ ' . number_format($cents / 100, 2, ',', '.');
        }

        return 'R$ 0,00';
    }
}
