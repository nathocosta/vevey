<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Coupons\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vevey\PanelAdmin\Filament\Resources\Coupons\CouponResource;

final class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;
}
