
<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view users list
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        // User can view their own profile or admin can view any
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create users.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only admins can create users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        // User can update their own profile or admin can update any
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        // Only admins can delete users, but cannot delete themselves
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can update the model's role.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function updateRole(User $user, User $model): bool
    {
        // Only super admins can update user roles
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the model's status.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function updateStatus(User $user, User $model): bool
    {
        // Only admins can update user status
        return $user->isAdmin();
    }
}
