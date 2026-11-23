<?php

declare(strict_types=1);

namespace Vevey\Orders\Enums;

use Filament\Support\Contracts\HasLabel;

enum CouponType: string implements HasLabel
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Percent => 'Percentual (%)',
            self::Fixed => 'Valor fixo (R$)',
        };
    }
}
