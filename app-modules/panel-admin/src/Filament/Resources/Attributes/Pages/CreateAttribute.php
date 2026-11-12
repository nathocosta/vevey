<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vevey\PanelAdmin\Filament\Resources\Attributes\AttributeResource;

final class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;
}
