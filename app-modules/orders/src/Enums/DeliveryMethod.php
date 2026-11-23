<?php

declare(strict_types=1);

namespace Vevey\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliveryMethod: string implements HasColor, HasLabel
{
    case Delivery = 'delivery';
    case Pickup = 'pickup';

    public function getLabel(): string
    {
        return match ($this) {
            self::Delivery => 'Entrega (Correios)',
            self::Pickup => 'Retirada na loja',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Delivery => 'info',
            self::Pickup => 'success',
        };
    }
}
