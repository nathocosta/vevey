<?php

declare(strict_types=1);

namespace Vevey\Permissions\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Vevey\Permissions\Models\Permission;
use Vevey\Permissions\Models\Role;

final class PermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'roles' => Role::class,
            'permissions' => Permission::class,
        ]);
    }
}
