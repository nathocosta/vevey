<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;

/**
 * Edits an integer-cents column (ADR 1) in reais: shows 12.50 for 1250 and saves 1250 back.
 */
final class MoneyInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0.01)
            ->step(0.01)
            ->prefix('R$')
            ->formatStateUsing(fn (mixed $state): ?string => is_numeric($state) ? number_format(((int) $state) / 100, 2, '.', '') : null)
            ->dehydrateStateUsing(fn (mixed $state): ?int => is_numeric($state) ? (int) round(((float) $state) * 100) : null);
    }
}
