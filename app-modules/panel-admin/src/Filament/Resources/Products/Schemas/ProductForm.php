<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Product;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel-admin::catalog.products.sections.details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('panel-admin::catalog.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label(__('panel-admin::catalog.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true),
                        TextInput::make('sku')
                            ->label(__('panel-admin::catalog.fields.sku'))
                            ->required()
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                        TextInput::make('barcode')
                            ->label(__('panel-admin::catalog.fields.barcode'))
                            ->maxLength(64)
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label(__('panel-admin::catalog.fields.description'))
                            ->rows(4)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('panel-admin::catalog.fields.is_active'))
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('panel-admin::catalog.products.sections.prices'))
                    ->schema([
                        MoneyInput::make('price_cents')
                            ->label(__('panel-admin::catalog.fields.price'))
                            ->required(),
                        MoneyInput::make('promo_price_cents')
                            ->label(__('panel-admin::catalog.fields.promo_price'))
                            ->lt('price_cents'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('panel-admin::catalog.products.sections.classification'))
                    ->schema([
                        Select::make('categories')
                            ->label(__('panel-admin::catalog.fields.categories'))
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Select::make('attributeValues')
                            ->label(__('panel-admin::catalog.fields.attribute_values'))
                            ->relationship(
                                'attributeValues',
                                'value',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->with('attribute'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (AttributeValue $record): string => sprintf('%s: %s', $record->attribute->name, $record->value),
                            )
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('panel-admin::catalog.products.sections.images'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->hiddenLabel()
                            ->collection(Product::IMAGES_COLLECTION)
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->maxSize(5120),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
