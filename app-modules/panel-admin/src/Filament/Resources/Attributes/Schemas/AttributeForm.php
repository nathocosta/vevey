<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * An attribute (Cor, Tipo) and its values (Azul, Rosa) in one form: a new attribute is a row in the panel, no migration (ADR 6).
 */
final class AttributeForm
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
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('panel-admin::catalog.attributes.values'))
                    ->schema([
                        Repeater::make('values')
                            ->hiddenLabel()
                            ->relationship('values')
                            ->schema([
                                TextInput::make('value')
                                    ->label(__('panel-admin::catalog.fields.value'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', Str::slug($state ?? ''))),
                                TextInput::make('slug')
                                    ->label(__('panel-admin::catalog.fields.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->distinct(),
                            ])
                            ->columns(2)
                            ->addActionLabel(__('panel-admin::catalog.attributes.add_value'))
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
