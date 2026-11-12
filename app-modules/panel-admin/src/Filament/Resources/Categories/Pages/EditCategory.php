<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Categories\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;
use Vevey\Catalog\Models\Category;
use Vevey\PanelAdmin\Filament\Resources\Categories\CategoryResource;

final class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (Category $record): bool => $record->children()->exists()),
        ];
    }
}
