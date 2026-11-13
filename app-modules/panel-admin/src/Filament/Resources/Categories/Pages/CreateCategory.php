<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Categories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vevey\PanelAdmin\Filament\Resources\Categories\CategoryResource;

final class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
