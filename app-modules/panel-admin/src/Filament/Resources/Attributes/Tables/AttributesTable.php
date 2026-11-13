<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes\Tables;

use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Vevey\Catalog\Models\Attribute;
use Vevey\PanelAdmin\Filament\Actions\GuardedDeleteAction;
use Vevey\PanelAdmin\Filament\Resources\Attributes\AttributeResource;

final class AttributesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('panel-admin::catalog.fields.name'))
                    ->weight(FontWeight::Medium)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('values.value')
                    ->label(__('panel-admin::catalog.attributes.values'))
                    ->badge()
                    ->limitList(8),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                GuardedDeleteAction::make(Attribute::class, fn (Attribute $record): ?string => AttributeResource::deletionBlockedReason($record)),
            ]);
    }
}
