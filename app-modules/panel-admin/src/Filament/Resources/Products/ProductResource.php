<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;
use Vevey\Catalog\Models\Product;
use Vevey\PanelAdmin\Filament\Resources\Products\Pages\CreateProduct;
use Vevey\PanelAdmin\Filament\Resources\Products\Pages\EditProduct;
use Vevey\PanelAdmin\Filament\Resources\Products\Pages\ListProducts;
use Vevey\PanelAdmin\Filament\Resources\Products\Schemas\ProductForm;
use Vevey\PanelAdmin\Filament\Resources\Products\Tables\ProductsTable;

/**
 * Access is decided by Vevey\Catalog\Policies\ProductPolicy (permissions from the catalog's rbac.php).
 */
final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $slug = 'products';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::catalog.products.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::catalog.products.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::catalog.navigation_group');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    /**
     * Trashed products stay reachable so they can be restored from the edit page.
     *
     * @return Builder<Model>
     */
    #[Override]
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    /**
     * @return list<string>
     */
    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'barcode'];
    }
}
