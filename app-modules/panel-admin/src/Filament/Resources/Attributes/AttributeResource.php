<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Catalog\Models\Attribute;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Pages\CreateAttribute;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Pages\EditAttribute;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Pages\ListAttributes;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Schemas\AttributeForm;
use Vevey\PanelAdmin\Filament\Resources\Attributes\Tables\AttributesTable;

/**
 * Access is decided by Vevey\Catalog\Policies\AttributePolicy (permissions from the catalog's rbac.php).
 */
final class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $slug = 'attributes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::catalog.attributes.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::catalog.attributes.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::catalog.navigation_group');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return AttributeForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return AttributesTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListAttributes::route('/'),
            'create' => CreateAttribute::route('/create'),
            'edit' => EditAttribute::route('/{record}/edit'),
        ];
    }
}
