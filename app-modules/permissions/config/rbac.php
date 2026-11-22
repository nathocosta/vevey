<?php

declare(strict_types=1);

use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;
use Vevey\Permissions\Models\Permission;
use Vevey\Permissions\Models\Role;

/*
 * Role => [Model::class => [PermissionsEnum, ...]] for the models of this module.
 * sync:permissions merges every app-modules/{module}/config/rbac.php; the super admin gets every permission anyway.
 */
return [
    'permissions' => [
        Roles::SuperAdmin->value => [
            Role::class => PermissionsEnum::cases(),
            Permission::class => PermissionsEnum::cases(),
        ],
    ],
];
