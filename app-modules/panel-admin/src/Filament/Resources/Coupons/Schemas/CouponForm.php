<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Vevey\Orders\Enums\CouponType;
use Vevey\PanelAdmin\Filament\Resources\Products\Schemas\MoneyInput;

final class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Regras do cupom')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Código')
                        ->required()
                        ->maxLength(40)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(static fn (?string $state): string => mb_strtoupper(mb_trim((string) $state)))
                        ->placeholder('Ex.: VEVEY10'),

                    Select::make('type')
                        ->label('Tipo de desconto')
                        ->options(CouponType::class)
                        ->default(CouponType::Percent->value)
                        ->required(),

                    TextInput::make('value')
                        ->label('Valor (% ou centavos se fixo)')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->required()
                        ->helperText('Para percentual, informe 1 a 100 (ex.: 10 = 10%). Para valor fixo, informe em centavos (ex.: 1500 = R$ 15,00).'),

                    MoneyInput::make('min_subtotal_cents')
                        ->label('Pedido mínimo')
                        ->required(),

                    TextInput::make('max_uses')
                        ->label('Limite de usos (opcional)')
                        ->numeric()
                        ->integer()
                        ->minValue(1)
                        ->nullable(),

                    Toggle::make('is_active')
                        ->label('Cupom ativo')
                        ->default(true),

                    DateTimePicker::make('starts_at')
                        ->label('Início da vigência')
                        ->nullable(),

                    DateTimePicker::make('expires_at')
                        ->label('Fim da vigência')
                        ->nullable(),
                ]),
        ]);
    }
}
