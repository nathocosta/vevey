<?php

declare(strict_types=1);

namespace Vevey\Permissions\Console\Commands\SyncPermissions;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Vevey\Permissions\Enums\PermissionsEnum;
use Vevey\Permissions\Enums\Roles;
use Vevey\Permissions\Models\Permission;
use Vevey\Permissions\Models\Role;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

/**
 * Builds the CRUD permissions of every model in the morph map, creates the roles of the Roles enum,
 * applies each module's config/rbac.php and gives every permission to the super admin.
 */
final class SyncPermissionsCommand extends Command
{
    public const string SUPER_ADMIN_EMAIL = 'admin@vevey.com';

    private const string GUARD = 'web';

    protected $signature = 'sync:permissions';

    protected $description = "Synchronize roles and permissions from the morph map and the modules' rbac.php files";

    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        intro('Synchronizing roles and permissions...');

        spin(
            callback: fn (): bool => $permissionRegistrar->forgetCachedPermissions(),
            message: 'Resetting cached roles and permissions...',
        );

        $models = $this->prepareModels();
        $rbacConfigs = $this->retrieveRolePermissionsFromModules($models);
        $permissions = $this->syncPermissions($models);

        $this->syncRoles();
        $this->syncRolesPermissions($rbacConfigs);
        $this->syncSuperAdminPermissions($permissions);

        outro('Roles and permissions synchronized successfully!');

        return self::SUCCESS;
    }

    private function syncRoles(): void
    {
        spin(
            callback: function (): void {
                foreach (Roles::cases() as $role) {
                    Role::query()->firstOrCreate([
                        'name' => $role->value,
                        'guard_name' => self::GUARD,
                    ]);
                }
            },
            message: 'Syncing base roles...',
        );
    }

    /**
     * @param  Collection<int, RolePermissions>  $rbacConfigs
     */
    private function syncRolesPermissions(Collection $rbacConfigs): void
    {
        if ($rbacConfigs->isEmpty()) {
            return;
        }

        progress(
            label: 'Syncing RBAC configurations...',
            steps: $rbacConfigs->all(),
            callback: function (RolePermissions $rbacConfig): void {
                $role = Role::query()->firstOrCreate([
                    'name' => $rbacConfig->role,
                    'guard_name' => self::GUARD,
                ]);

                $rolePermissions = [];
                foreach ($rbacConfig->resources as $resourceClass => $actions) {
                    foreach ($actions as $action) {
                        $rolePermissions[] = $action->buildPermissionFor($resourceClass);
                    }
                }

                if ($rolePermissions !== []) {
                    $role->syncPermissions($rolePermissions);
                }
            },
        );
    }

    /**
     * @return Collection<int, ModelPayload>
     */
    private function prepareModels(): Collection
    {
        return spin(
            callback: fn (): Collection => collect(Relation::morphMap())
                ->map(fn (string $modelPath, string|int $morphKey): ModelPayload => new ModelPayload(
                    name: (string) $morphKey,
                    resource: $modelPath,
                    resourceGroup: $this->resourceGroupFor($modelPath),
                ))
                ->values(),
            message: 'Preparing models from the morph map...',
        );
    }

    /**
     * The module name for `Vevey\{Module}\...` classes, `App` for everything else.
     */
    private function resourceGroupFor(string $modelPath): string
    {
        $segments = explode('\\', $modelPath);

        return $segments[0] === 'Vevey' && isset($segments[1]) ? $segments[1] : 'App';
    }

    /**
     * @param  Collection<int, ModelPayload>  $models
     * @return Collection<int, Permission>
     */
    private function syncPermissions(Collection $models): Collection
    {
        if ($models->isEmpty()) {
            return collect();
        }

        /** @var array<int, list<Permission>> $results */
        $results = progress(
            label: 'Generating permissions...',
            steps: $models->all(),
            callback: function (ModelPayload $modelPayload): array {
                $created = [];
                foreach (PermissionsEnum::cases() as $action) {
                    $created[] = Permission::query()->firstOrCreate([
                        ...$modelPayload->toArray(),
                        'name' => $action->buildPermissionFor($modelPayload->resource),
                        'guard_name' => self::GUARD,
                        'action' => $action,
                    ]);
                }

                return $created;
            },
        );

        /** @var Collection<int, Permission> $permissions */
        $permissions = collect($results)->flatten();

        note(sprintf('Permissions generated: %s', $permissions->count()));

        return $permissions;
    }

    /**
     * @param  Collection<int, Permission>  $permissions
     */
    private function syncSuperAdminPermissions(Collection $permissions): void
    {
        $assigned = spin(
            callback: function () use ($permissions): bool {
                $superAdminRole = Role::query()->firstOrCreate([
                    'name' => Roles::SuperAdmin->value,
                    'guard_name' => self::GUARD,
                ]);

                $superAdminRole->syncPermissions($permissions);

                $user = User::query()->where('email', self::SUPER_ADMIN_EMAIL)->first();
                if ($user instanceof User) {
                    $user->assignRole(Roles::SuperAdmin);

                    return true;
                }

                return false;
            },
            message: 'Syncing Super Admin permissions...',
        );

        if (! $assigned) {
            warning(sprintf('Default Super Admin user (%s) not found.', self::SUPER_ADMIN_EMAIL));
        }
    }

    /**
     * @param  Collection<int, ModelPayload>  $models
     * @return Collection<int, RolePermissions>
     */
    private function retrieveRolePermissionsFromModules(Collection $models): Collection
    {
        /** @var array<string, array<class-string, list<PermissionsEnum>>> $rbacConfigs */
        $rbacConfigs = [];
        $modulesWithRbac = [];

        spin(
            callback: function () use (&$rbacConfigs, &$modulesWithRbac): void {
                foreach (glob(base_path('app-modules/*/config/rbac.php')) ?: [] as $file) {
                    /** @var array{permissions?: array<string, array<class-string, list<PermissionsEnum>>>} $config */
                    $config = require $file;
                    $rbacConfigs = array_merge_recursive($rbacConfigs, $config['permissions'] ?? []);

                    $modulesWithRbac[str_replace('-', '', mb_strtolower(basename(dirname($file, 2))))] = true;
                }
            },
            message: 'Scanning modules for RBAC configurations...',
        );

        $tableData = $models->map(fn (ModelPayload $model): array => [
            $model->name,
            $model->resource,
            $model->resourceGroup,
            isset($modulesWithRbac[mb_strtolower($model->resourceGroup)]) ? '✓' : 'x',
        ])->all();

        note('Models and RBAC Status:');
        table(['Name', 'Resource', 'Group', 'Has RBAC'], $tableData);

        return collect($rbacConfigs)
            ->map(fn (array $resources, string $role): RolePermissions => new RolePermissions(
                role: $role,
                resources: $resources,
            ))
            ->values();
    }
}
