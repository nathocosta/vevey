<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;

/**
 * Edits an integer-cents column (ADR 1) in reais: shows 12.50 for 1250 and saves 1250 back.
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
            ->formatStateUsing(fn (mixed $state): ?string => is_numeric($state) ? number_format(((int) $state) / 100, 2, '.', '') : null)
            ->dehydrateStateUsing(fn (mixed $state): ?int => is_numeric($state) ? (int) round(((float) $state) * 100) : null);
    }
}
