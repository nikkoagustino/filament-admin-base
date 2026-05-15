<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Panel users may list all administrator accounts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Panel users may open any administrator profile from the table.
     */
    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Panel users may create new administrator accounts.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Panel users may update other administrator accounts (and their own profile).
     */
    public function update(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Prevent removing your own account while logged in.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->id !== $model->id;
    }

    /**
     * Allow bulk delete in the table; each row still checks {@see delete()} when using individual authorization.
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
