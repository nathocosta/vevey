<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Categories\Pages;

use Filament\Resources\Pages\EditRecord;
use Override;
use Vevey\Catalog\Models\Category;
use Vevey\PanelAdmin\Filament\Actions\GuardedDeleteAction;
use Vevey\PanelAdmin\Filament\Resources\Categories\CategoryResource;

final class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            GuardedDeleteAction::make(Category::class, fn (Category $record): ?string => CategoryResource::deletionBlockedReason($record)),
        ];
    }
}
