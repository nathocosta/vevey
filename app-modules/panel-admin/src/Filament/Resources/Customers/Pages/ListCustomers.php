<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\ListRecords;
use Vevey\PanelAdmin\Filament\Resources\Customers\CustomerResource;

final class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;
}
