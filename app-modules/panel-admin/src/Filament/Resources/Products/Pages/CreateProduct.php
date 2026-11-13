<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vevey\PanelAdmin\Filament\Resources\Products\ProductResource;

final class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
