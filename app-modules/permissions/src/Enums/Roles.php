<?php

declare(strict_types=1);

namespace Vevey\Permissions\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum Roles: string implements HasColor, HasDescription, HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Attendant = 'attendant';
    case Logistics = 'logistics';

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }

    public function isAttendant(): bool
    {
        return $this === self::Attendant;
    }

    public function isLogistics(): bool
    {
        return $this === self::Logistics;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Administrador',
            self::Attendant => 'Atendente',
            self::Logistics => 'Logística',
        };
    }

    /**
     * Access ramps light to red: the operational roles are lightest, the dev team's role is red.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Logistics => 'gray',
            self::Attendant => 'info',
            self::Admin => 'warning',
            self::SuperAdmin => 'danger',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Equipe de desenvolvimento: acesso total, inclusive papéis e permissões',
            self::Admin => 'Dono da loja: catálogo, estoque, pedidos, clientes e relatórios',
            self::Attendant => 'Balcão e pedidos: PDV, pedidos e clientes',
            self::Logistics => 'Separação e entrega de pedidos',
        };
    }
}
