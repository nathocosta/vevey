<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Roles;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Roles\Pages\CreateRole;
use Vevey\PanelAdmin\Filament\Resources\Roles\Pages\EditRole;
use Vevey\PanelAdmin\Filament\Resources\Roles\Pages\ListRoles;
use Vevey\PanelAdmin\Filament\Resources\Roles\Schemas\RoleForm;
use Vevey\PanelAdmin\Filament\Resources\Roles\Tables\RolesTable;
use Vevey\Permissions\Models\Role;

/**
 * Access is decided by Vevey\Permissions\Policies\RolePolicy (super admin only).
 */
final class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?int $navigationSort = 100;

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::roles.resource.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::roles.resource.plural_label');
    }

    #[Override]
    public static function getNavigationLabel(): string
    {
        return __('panel-admin::roles.resource.navigation_label');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    /**
     * @return list<string>
     */
    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}
