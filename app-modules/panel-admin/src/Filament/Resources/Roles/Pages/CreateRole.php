<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vevey\PanelAdmin\Filament\Resources\Roles\RoleResource;

final class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
