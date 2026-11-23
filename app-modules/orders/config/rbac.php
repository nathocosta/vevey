<?php

declare(strict_types=1);

use Vevey\Orders\Models\Coupon;
use Vevey\Orders\Models\Order;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;

/*
 * Role => [Model::class => [PermissionsEnum, ...]] for the orders module.
 * Orders are historical records and are never deleted by staff; Admin and Attendant manage orders,
 * Logistics views and updates fulfillment progress.
 */
$readAndUpdate = [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Update];
$manageCoupons = [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Create, PermissionsEnum::Update, PermissionsEnum::Delete];

return [
    'permissions' => [
        Roles::Admin->value => [
            Order::class => [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Create, PermissionsEnum::Update],
            Coupon::class => $manageCoupons,
        ],
        Roles::Attendant->value => [
            Order::class => [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Create, PermissionsEnum::Update],
            Coupon::class => [PermissionsEnum::ViewAny, PermissionsEnum::View],
        ],
        Roles::Logistics->value => [
            Order::class => $readAndUpdate,
        ],
    ],
];
