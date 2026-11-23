<?php

declare(strict_types=1);

namespace Vevey\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderChannel: string implements HasColor, HasLabel
{
    case Web = 'web';
    case Pos = 'pos';
    case Whatsapp = 'whatsapp';

    public function getLabel(): string
    {
        return match ($this) {
            self::Web => 'Site',
            self::Pos => 'Balcão (PDV)',
            self::Whatsapp => 'WhatsApp',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Web => 'info',
            self::Pos => 'warning',
            self::Whatsapp => 'success',
        };
    }
}
