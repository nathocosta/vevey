<?php

declare(strict_types=1);

namespace Vevey\Orders\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Pix = 'pix';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case Cash = 'cash';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pix => 'Pix',
            self::CreditCard => 'Cartão de crédito',
            self::DebitCard => 'Cartão de débito',
            self::Cash => 'Dinheiro',
        };
    }
}
