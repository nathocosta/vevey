<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Actions;

use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Product;

/**
 * "Duplicar produto" (ADR 6): registers the next color of a product in series. The copy gets a new name, slug and SKU,
 * no barcode, and keeps the prices, categories and attribute values (the operator then swaps the color). Images stay behind.
 */
final class DuplicateProductAction
{
    public static function make(): ReplicateAction
    {
        return ReplicateAction::make('duplicate')
            ->label(__('panel-admin::catalog.products.duplicate.label'))
            ->modalHeading(__('panel-admin::catalog.products.duplicate.heading'))
            ->excludeAttributes(['barcode', 'deleted_at'])
            ->hidden(fn (Product $record): bool => $record->trashed())
            ->mutateRecordDataUsing(fn (array $data): array => [
                ...$data,
                'name' => $data['name'] . ' (' . __('panel-admin::catalog.products.duplicate.copy_suffix') . ')',
                'slug' => $data['slug'] . '-copia',
                'sku' => $data['sku'] . '-COPIA',
            ])
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
                    ->unique(Product::class, 'slug'),
                TextInput::make('sku')
                    ->label(__('panel-admin::catalog.fields.sku'))
                    ->required()
                    ->maxLength(64)
                    ->unique(Product::class, 'sku'),
            ])
            ->after(function (Product $record, Product $replica): void {
                $replica->categories()->sync($record->categories()->pluck('categories.id'));
                $replica->attributeValues()->sync($record->attributeValues()->pluck('attribute_values.id'));
            });
    }
}
