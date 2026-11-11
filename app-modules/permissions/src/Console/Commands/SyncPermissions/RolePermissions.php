<?php

declare(strict_types=1);

namespace Vevey\Permissions\Console\Commands\SyncPermissions;

use Vevey\Permissions\Enums\PermissionsEnum;

final readonly class RolePermissions
{
    /**
     * @param  array<class-string, list<PermissionsEnum>>  $resources
     */
    public function __construct(
        public string $role,
        public array $resources,
    ) {}
}
