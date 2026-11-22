<?php

declare(strict_types=1);

namespace Vevey\Permissions\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as BasePermission;
use Vevey\Permissions\Database\Factories\PermissionFactory;
use Vevey\Permissions\Enums\PermissionsEnum;

/**
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property string $resource
 * @property string $resource_group
 * @property PermissionsEnum $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $resource_model
 * @property-read string $formatted_name
 */
#[UseFactory(PermissionFactory::class)]
#[Table(name: 'rbac_permissions')]
final class Permission extends BasePermission
{
    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => PermissionsEnum::class,
        ];
    }

    /**
     * Short class name of the resource, e.g. `User` for `App\Models\User`.
     *
     * @return Attribute<string, never>
     */
    protected function resourceModel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => class_basename($this->resource),
        );
    }

    /**
     * `{group}-{model}-{action}-{name}`, parsed by the permissions checkbox list in the admin panel.
     *
     * @return Attribute<string, never>
     */
    protected function formattedName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => sprintf(
                '%s-%s-%s-%s', $this->resource_group, $this->resource_model, $this->action->value, $this->name
            ),
        );
    }
}
