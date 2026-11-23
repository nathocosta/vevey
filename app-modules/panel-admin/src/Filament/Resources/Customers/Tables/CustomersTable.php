<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Customers\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Vevey\Customers\Models\Customer;

final class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->with('identities')->withCount('orders'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('identities.value')
                    ->label('Contatos (ADR 7)')
                    ->badge()
                    ->limitList(2)
                    ->searchable(query: static function (Builder $query, string $search): Builder {
                        $digits = (string) preg_replace('/\D+/', '', $search);

                        return $query->whereHas('identities', static function (Builder $q) use ($search, $digits): void {
                            $q->where('value', 'like', "%{$search}%");
                            if ($digits !== '') {
                                $q->orWhere('value', 'like', "%{$digits}%");
                            }
                        });
                    }),
                IconColumn::make('has_login')
                    ->label('Conta ativa')
                    ->boolean()
                    ->getStateUsing(static fn (Customer $record): bool => $record->password !== null),
                TextColumn::make('orders_count')
                    ->label('Pedidos')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
