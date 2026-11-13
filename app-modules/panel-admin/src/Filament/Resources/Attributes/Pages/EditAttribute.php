<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Attributes\Pages;

use Filament\Resources\Pages\EditRecord;
use Override;
use Vevey\Catalog\Models\Attribute;
use Vevey\PanelAdmin\Filament\Actions\GuardedDeleteAction;
use Vevey\PanelAdmin\Filament\Resources\Attributes\AttributeResource;

final class EditAttribute extends EditRecord
{
    protected static string $resource = AttributeResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            GuardedDeleteAction::make(Attribute::class, fn (Attribute $record): ?string => AttributeResource::deletionBlockedReason($record)),
        ];
    }
}
