<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Coupons\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;
use Vevey\PanelAdmin\Filament\Resources\Coupons\CouponResource;

final class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
