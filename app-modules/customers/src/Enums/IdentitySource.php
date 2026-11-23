<?php

declare(strict_types=1);

namespace Vevey\Customers\Enums;

use Filament\Support\Contracts\HasLabel;

enum IdentitySource: string implements HasLabel
{
    case Web = 'web';
    case Pos = 'pos';
    case Whatsapp = 'whatsapp';
    case Instagram = 'instagram';
    case Import = 'import';

    public function getLabel(): string
    {
        return match ($this) {
            self::Web => 'Site',
            self::Pos => 'Balcão (PDV)',
            self::Whatsapp => 'WhatsApp',
            self::Instagram => 'Instagram',
            self::Import => 'Importação',
        };
    }
}
