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
use Vevey\Catalog\Models\Product;
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

    /**
     * How many products or subcategories the "in use" message names before summing up the rest.
     */
    private const int NAMES_SHOWN = 5;

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

    /**
     * Why the category cannot be deleted, naming what still uses it, or null when it can (no silent unlinking of products).
     */
    public static function deletionBlockedReason(Category $category): ?string
    {
        $category->loadCount(['products', 'children']);
        $productsCount = (int) $category->getAttribute('products_count');
        $childrenCount = (int) $category->getAttribute('children_count');

        if ($productsCount === 0 && $childrenCount === 0) {
            return null;
        }

        $usage = [];

        if ($productsCount > 0) {
            $usage[] = __('panel-admin::catalog.in_use.category_products', [
                'count' => $productsCount,
                'names' => self::namesWithRemainder($category->products()->orderBy('name')->limit(self::NAMES_SHOWN)->get()->map(fn (Product $product): string => $product->name)->all(), $productsCount),
            ]);
        }

        if ($childrenCount > 0) {
            $usage[] = __('panel-admin::catalog.in_use.category_children', [
                'count' => $childrenCount,
                'names' => self::namesWithRemainder($category->children()->orderBy('name')->limit(self::NAMES_SHOWN)->get()->map(fn (Category $child): string => $child->name)->all(), $childrenCount),
            ]);
        }

        return __('panel-admin::catalog.in_use.category', [
            'name' => $category->name,
            'usage' => implode('; ', $usage),
        ]);
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

    /**
     * @param  array<int, string>  $names  the first names, at most NAMES_SHOWN
     */
    private static function namesWithRemainder(array $names, int $total): string
    {
        $listed = implode(', ', $names);
        $remainder = $total - count($names);

        return $remainder > 0
            ? __('panel-admin::catalog.in_use.and_more', ['names' => $listed, 'count' => $remainder])
            : $listed;
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
