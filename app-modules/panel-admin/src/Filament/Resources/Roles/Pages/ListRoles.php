<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Roles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Roles\RoleResource;

final class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
