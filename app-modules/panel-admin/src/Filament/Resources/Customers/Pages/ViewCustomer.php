<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\ViewRecord;
use Vevey\PanelAdmin\Filament\Resources\Customers\CustomerResource;

final class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;
}
