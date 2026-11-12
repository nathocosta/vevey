<?php

declare(strict_types=1);

namespace Vevey\Permissions\Policies;

use App\Models\User;
use Vevey\Permissions\Enums\PermissionsEnum;

/**
 * CRUD policy backed by the permissions sync:permissions generates for a morph-mapped model.
 * A module policy extends it and names its model; every ability checks `{action}_{morph alias}`.
 */
abstract class ModelPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::ViewAny);
    }

    public function view(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::View);
    }

    public function create(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Create);
    }

    public function update(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Update);
    }

    public function delete(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Delete);
    }

    public function restore(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Restore);
    }

    public function forceDelete(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::ForceDelete);
    }

    /**
     * @return class-string
     */
    abstract protected function model(): string;

    private function allows(User $user, PermissionsEnum $action): bool
    {
        return $user->can($action->buildPermissionFor($this->model()));
    }
}
