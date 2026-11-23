<?php

declare(strict_types=1);

namespace Vevey\Orders\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdjustmentType: string implements HasLabel
{
    case Coupon = 'coupon';
    case PixDiscount = 'pix_discount';
    case FreeShipping = 'free_shipping';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Coupon => 'Cupom',
            self::PixDiscount => 'Desconto Pix',
            self::FreeShipping => 'Frete grátis',
            self::Manual => 'Ajuste manual',
        };
    }
}
