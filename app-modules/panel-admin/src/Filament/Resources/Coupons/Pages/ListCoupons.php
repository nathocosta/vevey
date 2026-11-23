<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Coupons\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Coupons\CouponResource;

final class ListCoupons extends ListRecords
{
    protected static string $resource = CouponResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
