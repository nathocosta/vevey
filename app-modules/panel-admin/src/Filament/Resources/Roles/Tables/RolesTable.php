<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Vevey\Permissions\Enums\Roles;

final class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('panel-admin::roles.fields.name'))
                    ->weight(FontWeight::Medium)
                    ->formatStateUsing(fn (string $state): string => Roles::tryFrom($state)?->getLabel() ?? $state)
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->label(__('panel-admin::roles.fields.guard_name'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('permissions_count')
                    ->label(__('panel-admin::roles.fields.permissions'))
                    ->counts('permissions')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->label(__('panel-admin::roles.fields.updated_at'))
                    ->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
