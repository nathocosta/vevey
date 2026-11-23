<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Orders\Pages;

use Filament\Resources\Pages\ListRecords;
use Vevey\PanelAdmin\Filament\Resources\Orders\OrderResource;

final class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;
}
