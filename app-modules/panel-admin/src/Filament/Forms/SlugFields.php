<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * A name field that fills the slug next to it when it loses focus, and that slug field. The caller adds the slug's
 * uniqueness rule, which depends on where it lives (resource, repeater, duplicate action).
 */
final class SlugFields
{
    public static function source(string $name = 'name', string $slug = 'slug'): TextInput
    {
        return TextInput::make($name)
            ->label(__('panel-admin::catalog.fields.' . $name))
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set($slug, Str::slug($state ?? '')));
    }

    public static function slug(string $slug = 'slug'): TextInput
    {
        return TextInput::make($slug)
            ->label(__('panel-admin::catalog.fields.slug'))
            ->required()
            ->maxLength(255)
            ->alphaDash();
    }
}
