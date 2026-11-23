<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Customers\Schemas;

use Brick\Money\Money;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dados do cliente')
                ->columns(4)
                ->schema([
                    TextEntry::make('name')->label('Nome')->weight('bold'),
                    TextEntry::make('email')->label('E-mail')->placeholder('—'),
                    TextEntry::make('cpf')->label('CPF')->placeholder('—'),
                    TextEntry::make('created_at')->label('Cliente desde')->dateTime('d/m/Y H:i'),
                ]),

            Section::make('Identidades e contatos (ADR 7)')
                ->schema([
                    RepeatableEntry::make('identities')
                        ->label('')
                        ->columns(4)
                        ->schema([
                            TextEntry::make('type')->label('Canal')->badge(),
                            TextEntry::make('value')->label('Contato')->fontFamily('mono'),
                            TextEntry::make('source')->label('Origem'),
                            TextEntry::make('retired_at')
                                ->label('Status')
                                ->badge()
                                ->formatStateUsing(static fn ($state): string => $state === null ? 'Ativo' : 'Aposentado')
                                ->color(static fn ($state): string => $state === null ? 'success' : 'gray'),
                        ]),
                ]),

            Section::make('Endereços cadastrados')
                ->schema([
                    RepeatableEntry::make('addresses')
                        ->label('')
                        ->columns(3)
                        ->schema([
                            TextEntry::make('label')->label('Rótulo')->badge(),
                            TextEntry::make('street')
                                ->label('Endereço')
                                ->formatStateUsing(static fn ($state, $record): string => "{$record->street}, {$record->number} · {$record->neighborhood}"),
                            TextEntry::make('city')
                                ->label('Cidade / CEP')
                                ->formatStateUsing(static fn ($state, $record): string => "{$record->city}/{$record->state} · CEP {$record->cep}"),
                        ]),
                ]),

            Section::make('Pedidos do cliente')
                ->schema([
                    RepeatableEntry::make('orders')
                        ->label('')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('number')->label('Número')->fontFamily('mono')->weight('semibold'),
                            TextEntry::make('channel')->label('Canal')->badge(),
                            TextEntry::make('payment_status')->label('Pagamento')->badge(),
                            TextEntry::make('fulfillment_status')->label('Entrega')->badge(),
                            TextEntry::make('total_cents')
                                ->label('Total')
                                ->formatStateUsing(static fn ($state): string => $state instanceof Money
                                    ? 'R$ ' . number_format($state->getMinorAmount()->toInt() / 100, 2, ',', '.')
                                    : 'R$ 0,00'),
                        ]),
                ]),
        ]);
    }
}
