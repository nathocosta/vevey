<?php

declare(strict_types=1);

namespace Vevey\Inventory\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReservationStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Fulfilled = 'fulfilled';
    case Released = 'released';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Ativa',
            self::Fulfilled => 'Baixada',
            self::Released => 'Liberada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'warning',
            self::Fulfilled => 'success',
            self::Released => 'gray',
        };
    }
}
