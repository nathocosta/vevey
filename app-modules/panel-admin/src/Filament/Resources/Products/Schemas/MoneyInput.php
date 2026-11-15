<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Schemas;

use App\Casts\MoneyCast;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Filament\Forms\Components\TextInput;

/**
 * Edits a Money column (integer cents, ADR 1) in reais: shows 12.50 for 1250 cents and saves a Money back.
 * At most two decimals, so what is validated (e.g. promo lower than price) is exactly what is stored.
 */
final class MoneyInput
{
    public const int MAX_REAIS = 1_000_000;

    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0.01)
            ->maxValue(self::MAX_REAIS)
            ->rule('decimal:0,2')
            ->step(0.01)
            ->prefix('R$')
            ->formatStateUsing(fn (mixed $state): ?string => match (true) {
                $state instanceof Money => (string) $state->getAmount(),
                is_numeric($state) => (string) Money::ofMinor((int) $state, MoneyCast::CURRENCY)->getAmount(),
                default => null,
            })
            ->dehydrateStateUsing(fn (mixed $state): ?Money => is_numeric($state)
                ? Money::of((string) $state, MoneyCast::CURRENCY, roundingMode: RoundingMode::HalfUp)
                : null);
    }
}
