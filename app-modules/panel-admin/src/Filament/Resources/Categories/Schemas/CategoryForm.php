<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Category;

/**
 * Categories have one level of nesting: a parent can only be a top-level category other than itself.
 */
final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
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
                        Select::make('parent_id')
                            ->label(__('panel-admin::catalog.fields.parent'))
                            ->relationship(
                                'parent',
                                'name',
                                modifyQueryUsing: fn (Builder $query, ?Category $record): Builder => $query
                                    ->whereNull('parent_id')
                                    ->when($record, fn (Builder $query, Category $record): Builder => $query->whereKeyNot($record->id)),
                            )
                            ->disabled(fn (?Category $record): bool => $record?->children()->exists() ?? false)
                            ->helperText(__('panel-admin::catalog.categories.parent_help'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('position')
                            ->label(__('panel-admin::catalog.fields.position'))
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Textarea::make('description')
                            ->label(__('panel-admin::catalog.fields.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('panel-admin::catalog.fields.is_active'))
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
