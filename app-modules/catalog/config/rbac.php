<?php

declare(strict_types=1);

use Vevey\Catalog\Models\Attribute;
use Vevey\Catalog\Models\AttributeValue;
use Vevey\Catalog\Models\Category;
use Vevey\Catalog\Models\Product;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;

/*
 * Role => [Model::class => [PermissionsEnum, ...]] for the models of this module.
 * The owner manages the catalog; products are never deleted for good (order lines reference them), so force delete
 * stays with the super admin. Counter and logistics staff only read it.
 */
$manage = [PermissionsEnum::ViewAny, PermissionsEnum::View, PermissionsEnum::Create, PermissionsEnum::Update, PermissionsEnum::Delete];
$read = [PermissionsEnum::ViewAny, PermissionsEnum::View];

return [
    'permissions' => [
        Roles::Admin->value => [
            Product::class => [...$manage, PermissionsEnum::Restore],
            Category::class => $manage,
            Attribute::class => $manage,
            AttributeValue::class => $manage,
        ],
        Roles::Attendant->value => [
            Product::class => $read,
            Category::class => $read,
            Attribute::class => $read,
            AttributeValue::class => $read,
        ],
        Roles::Logistics->value => [
            Product::class => $read,
            Category::class => $read,
            Attribute::class => $read,
            AttributeValue::class => $read,
        ],
    ],
];
