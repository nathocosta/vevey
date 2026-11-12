<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Products\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Products\Actions\DuplicateProductAction;
use Vevey\PanelAdmin\Filament\Resources\Products\ProductResource;

final class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DuplicateProductAction::make(),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
