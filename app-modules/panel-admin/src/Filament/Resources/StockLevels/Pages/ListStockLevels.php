<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\StockLevels\Pages;

use Filament\Resources\Pages\ListRecords;
use Vevey\PanelAdmin\Filament\Resources\StockLevels\StockLevelResource;

final class ListStockLevels extends ListRecords
{
    protected static string $resource = StockLevelResource::class;
}
