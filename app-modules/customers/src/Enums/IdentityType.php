<?php

declare(strict_types=1);

namespace Vevey\Customers\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IdentityType: string implements HasColor, HasLabel
{
    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case Email = 'email';
    case Instagram = 'instagram';

    public function getLabel(): string
    {
        return match ($this) {
            self::Phone => 'Telefone',
            self::Whatsapp => 'WhatsApp',
            self::Email => 'E-mail',
            self::Instagram => 'Instagram',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Phone => 'info',
            self::Whatsapp => 'success',
            self::Email => 'gray',
            self::Instagram => 'warning',
        };
    }
}
