<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Vevey\Catalog\Models\Product;
use Vevey\PanelAdmin\Filament\Resources\Products\Actions\DuplicateProductAction;

final class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label('')
                    ->collection(Product::IMAGES_COLLECTION)
                    ->conversion(Product::THUMB_CONVERSION)
                    ->limit(1)
                    ->square(),
                TextColumn::make('sku')
                    ->label(__('panel-admin::catalog.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('panel-admin::catalog.fields.name'))
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_cents')
                    ->label(__('panel-admin::catalog.fields.price'))
                    ->money('BRL', divideBy: 100)
                    ->sortable(),
                TextColumn::make('promo_price_cents')
                    ->label(__('panel-admin::catalog.fields.promo_price'))
                    ->money('BRL', divideBy: 100)
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label(__('panel-admin::catalog.fields.categories'))
                    ->badge()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('panel-admin::catalog.fields.is_active'))
                    ->boolean(),
                TextColumn::make('barcode')
                    ->label(__('panel-admin::catalog.fields.barcode'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('panel-admin::catalog.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('panel-admin::catalog.fields.is_active')),
                SelectFilter::make('categories')
                    ->label(__('panel-admin::catalog.fields.categories'))
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    DuplicateProductAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
