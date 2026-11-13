<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes\Schemas;

use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\PanelAdmin\Filament\Resources\Attributes\AttributeResource;

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
                            ->rule(fn (?Attribute $record): Closure => self::forbidRemovingValuesInUse($record))
                            ->addActionLabel(__('panel-admin::catalog.attributes.add_value'))
                            ->defaultItems(0),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Saving the repeater deletes the values that were removed from it; a value products use cannot go (no silent unlinking).
     * Existing items are keyed `record-{id}` in the repeater state.
     */
    private static function forbidRemovingValuesInUse(?Attribute $attribute): Closure
    {
        return function (string $field, mixed $state, Closure $fail) use ($attribute): void {
            if (! $attribute instanceof Attribute) {
                return;
            }

            $keptIds = collect(is_array($state) ? array_keys($state) : [])
                ->map(fn (int|string $key): string => Str::after((string) $key, 'record-'));

            $removedInUse = $attribute->valuesInUse()
                ->reject(fn (AttributeValue $value): bool => $keptIds->contains($value->id));

            if ($removedInUse->isNotEmpty()) {
                $fail(__('panel-admin::catalog.in_use.attribute_values', [
                    'values' => AttributeResource::describeUsage($removedInUse),
                ]));
            }
        };
    }
}
