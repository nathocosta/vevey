<?php

declare(strict_types=1);

namespace Vevey\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FulfillmentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Recebido',
            self::Preparing => 'Em separação',
            self::Ready => 'Pronto p/ retirada',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregue',
            self::Cancelled => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Preparing => 'info',
            self::Ready => 'warning',
            self::Shipped => 'primary',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }
}
