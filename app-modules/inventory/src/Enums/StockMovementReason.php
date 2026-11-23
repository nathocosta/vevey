<?php

declare(strict_types=1);

namespace Vevey\Inventory\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockMovementReason: string implements HasColor, HasLabel
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Entrada (Compra)',
            self::Sale => 'Venda',
            self::Return => 'Devolução',
            self::Adjustment => 'Ajuste / Contagem',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Purchase => 'success',
            self::Sale => 'info',
            self::Return => 'warning',
            self::Adjustment => 'gray',
        };
    }
}
