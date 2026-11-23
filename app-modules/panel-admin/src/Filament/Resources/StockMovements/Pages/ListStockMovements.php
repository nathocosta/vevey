<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\StockMovements\Pages;

use Filament\Resources\Pages\ListRecords;
use Vevey\PanelAdmin\Filament\Resources\StockMovements\StockMovementResource;

final class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;
}
