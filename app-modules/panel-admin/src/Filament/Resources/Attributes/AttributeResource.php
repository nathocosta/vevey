<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Override;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
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

    /**
     * Why the attribute cannot be deleted, or null when it can: deleting it would take its values off the products.
     */
    public static function deletionBlockedReason(Attribute $attribute): ?string
    {
        $valuesInUse = $attribute->valuesInUse();

        if ($valuesInUse->isEmpty()) {
            return null;
        }

        return __('panel-admin::catalog.in_use.attribute', [
            'name' => $attribute->name,
            'values' => self::describeUsage($valuesInUse),
        ]);
    }

    /**
     * @param  Collection<int, AttributeValue>  $values  loaded with `products_count`
     */
    public static function describeUsage(Collection $values): string
    {
        return $values
            ->map(fn (AttributeValue $value): string => __('panel-admin::catalog.in_use.value_usage', [
                'value' => $value->value,
                'count' => $value->getAttribute('products_count'),
            ]))
            ->implode(', ');
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
