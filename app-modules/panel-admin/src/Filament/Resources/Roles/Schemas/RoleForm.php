<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Vevey\Permissions\Models\Permission;

final class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('panel-admin::roles.fields.name'))
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),

                        TextInput::make('guard_name')
                            ->label(__('panel-admin::roles.fields.guard_name'))
                            ->default('web')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('panel-admin::roles.fields.permissions'))
                    ->schema([
                        PermissionsCheckboxList::make('permissions')
                            ->hiddenLabel()
                            ->relationship('permissions', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Permission $record): string => $record->formatted_name)
                            ->bulkToggleable(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
