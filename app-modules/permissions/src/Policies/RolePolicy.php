<?php

declare(strict_types=1);

namespace Vevey\Permissions\Policies;

use App\Models\User;
use Vevey\Permissions\Enums\Roles;

/**
 * Roles and their permissions are managed only by the dev team.
 */
final class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function view(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function update(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function delete(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function restore(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function forceDelete(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function replicate(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole(Roles::SuperAdmin);
    }
}
