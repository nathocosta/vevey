<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Products\ProductResource;

final class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
