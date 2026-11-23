<?php

declare(strict_types=1);

use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;

/*
 * Role => [Model::class => [PermissionsEnum, ...]] for the inventory module.
 * Movements are append-only (never updated or deleted). Admin and Logistics can view and adjust stock.
 */
$readOnly = [PermissionsEnum::ViewAny, PermissionsEnum::View];

return [
    'permissions' => [
        Roles::Admin->value => [
            StockLevel::class => [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Update],
            StockMovement::class => [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Create],
        ],
        Roles::Attendant->value => [
            StockLevel::class => $readOnly,
            StockMovement::class => $readOnly,
        ],
        Roles::Logistics->value => [
            StockLevel::class => [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Update],
            StockMovement::class => [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Create],
        ],
    ],
];
