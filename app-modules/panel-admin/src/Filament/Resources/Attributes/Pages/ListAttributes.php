<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Attributes\AttributeResource;

final class ListAttributes extends ListRecords
{
    protected static string $resource = AttributeResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
