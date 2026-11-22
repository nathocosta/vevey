<?php

declare(strict_types=1);

namespace Vevey\Permissions\Policies;

use App\Models\User;
use Vevey\Permissions\Enums\PermissionsEnum;

/**
 * CRUD policy backed by the permissions sync:permissions generates for a morph-mapped model.
 * A module policy extends it and names its model; every ability checks `{action}_{morph alias}`.
 * Besides CRUD it answers every ability Filament asks (bulk, replicate, reorder): the panel runs in
 * strict authorization, so a missing method fails loudly instead of allowing the action.
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

    public function deleteAny(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Delete);
    }

    public function restoreAny(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Restore);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::ForceDelete);
    }

    /**
     * Duplicating a record creates a new one.
     */
    public function replicate(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Create);
    }

    /**
     * Reordering a table changes the position of the records.
     */
    public function reorder(User $user): bool
    {
        return $this->allows($user, PermissionsEnum::Update);
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
