<?php

declare(strict_types=1);

namespace Vevey\Permissions\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as BaseRole;
use Vevey\Permissions\Database\Factories\RoleFactory;
use Vevey\Permissions\Policies\RolePolicy;

/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Permission> $permissions
 */
#[UseFactory(RoleFactory::class)]
#[UsePolicy(RolePolicy::class)]
#[Table(name: 'rbac_roles')]
final class Role extends BaseRole
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    use HasUuids;
}
