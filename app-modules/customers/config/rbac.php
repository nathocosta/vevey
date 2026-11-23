<?php

declare(strict_types=1);

use Vevey\Customers\Models\Customer;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;

/*
 * Role => [Model::class => [PermissionsEnum, ...]] for the customers module.
 * Customers are soft-deleted only; Admin and Attendant manage customers, Logistics has read-only access.
 */
return [
    'permissions' => [
        Roles::Admin->value => [
            Customer::class => [
                PermissionsEnum::ViewAny,
                PermissionsEnum::View,
                PermissionsEnum::Create,
                PermissionsEnum::Update,
                PermissionsEnum::Delete,
                PermissionsEnum::Restore,
            ],
        ],
        Roles::Attendant->value => [
            Customer::class => [
                PermissionsEnum::ViewAny,
                PermissionsEnum::View,
                PermissionsEnum::Create,
                PermissionsEnum::Update,
            ],
        ],
        Roles::Logistics->value => [
            Customer::class => [
                PermissionsEnum::ViewAny,
                PermissionsEnum::View,
            ],
        ],
    ],
];
