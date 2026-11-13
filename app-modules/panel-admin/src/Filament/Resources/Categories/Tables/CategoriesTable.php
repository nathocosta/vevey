<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Categories\Tables;

use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Vevey\Catalog\Models\Category;
use Vevey\PanelAdmin\Filament\Actions\GuardedDeleteAction;
use Vevey\PanelAdmin\Filament\Resources\Categories\CategoryResource;

final class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('panel-admin::catalog.fields.name'))
                    ->weight(FontWeight::Medium)
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label(__('panel-admin::catalog.fields.parent'))
                    ->placeholder('—'),
                TextColumn::make('products_count')
                    ->label(__('panel-admin::catalog.products.plural_label'))
                    ->counts('products')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label(__('panel-admin::catalog.fields.is_active'))
                    ->boolean(),
                TextColumn::make('position')
                    ->label(__('panel-admin::catalog.fields.position'))
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->recordActions([
                EditAction::make(),
                GuardedDeleteAction::make(Category::class, fn (Category $record): ?string => CategoryResource::deletionBlockedReason($record)),
            ]);
    }
}
