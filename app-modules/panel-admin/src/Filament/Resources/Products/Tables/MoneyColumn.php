<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Tables;

use Brick\Money\Money;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * Shows a Money column (integer cents, ADR 1) in reais. The state is read in cents so Filament's money() formats it
 * and sorting still runs on the column.
 */
final class MoneyColumn
{
    public static function make(string $name): TextColumn
    {
        return TextColumn::make($name)
            ->getStateUsing(fn (Model $record): ?int => ($money = $record->getAttribute($name)) instanceof Money
                ? $money->getMinorAmount()->toInt()
                : null)
            ->money('BRL', divideBy: 100);
    }
}
