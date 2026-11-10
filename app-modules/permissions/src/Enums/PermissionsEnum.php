<?php

declare(strict_types=1);

namespace Vevey\Permissions\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

enum PermissionsEnum: string implements HasColor, HasDescription, HasLabel
{
    case View = 'view';
    case ViewAny = 'view_any';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';
    case ForceDelete = 'force_delete';

    public function isView(): bool
    {
        return $this === self::View;
    }

    public function isViewAny(): bool
    {
        return $this === self::ViewAny;
    }

    public function isCreate(): bool
    {
        return $this === self::Create;
    }

    public function isUpdate(): bool
    {
        return $this === self::Update;
    }

    public function isDelete(): bool
    {
        return $this === self::Delete;
    }

    public function isRestore(): bool
    {
        return $this === self::Restore;
    }

    public function isForceDelete(): bool
    {
        return $this === self::ForceDelete;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::View => 'Ver',
            self::ViewAny => 'Listar',
            self::Create => 'Criar',
            self::Update => 'Editar',
            self::Delete => 'Excluir',
            self::Restore => 'Restaurar',
            self::ForceDelete => 'Excluir definitivamente',
        };
    }

    /**
     * Impact ramps light to red: reading is lightest, permanent deletion is red.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::ViewAny, self::View => 'gray',
            self::Create => 'success',
            self::Update => 'info',
            self::Restore => 'primary',
            self::Delete => 'warning',
            self::ForceDelete => 'danger',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::View => 'Abrir um registro',
            self::ViewAny => 'Ver a listagem',
            self::Create => 'Cadastrar novos registros',
            self::Update => 'Alterar registros existentes',
            self::Delete => 'Excluir registros (reversível quando o model usa soft delete)',
            self::Restore => 'Restaurar registros excluídos',
            self::ForceDelete => 'Apagar registros de vez, sem volta',
        };
    }

    /**
     * Permission name for a model class: the action plus the model's morph alias, e.g. `view_any_users`.
     */
    public function buildPermissionFor(string $classPath): string
    {
        $morphAlias = array_search($classPath, Relation::morphMap(), strict: true);

        $resource = is_string($morphAlias) ? $morphAlias : $classPath;

        return $this->value . '_' . Str::snake($resource);
    }
}
