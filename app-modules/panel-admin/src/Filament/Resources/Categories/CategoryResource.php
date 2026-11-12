<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Categories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Catalog\Models\Category;
use Vevey\PanelAdmin\Filament\Resources\Categories\Pages\CreateCategory;
use Vevey\PanelAdmin\Filament\Resources\Categories\Pages\EditCategory;
use Vevey\PanelAdmin\Filament\Resources\Categories\Pages\ListCategories;
use Vevey\PanelAdmin\Filament\Resources\Categories\Schemas\CategoryForm;
use Vevey\PanelAdmin\Filament\Resources\Categories\Tables\CategoriesTable;

/**
 * Access is decided by Vevey\Catalog\Policies\CategoryPolicy (permissions from the catalog's rbac.php).
 */
final class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $slug = 'categories';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::catalog.categories.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::catalog.categories.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::catalog.navigation_group');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
