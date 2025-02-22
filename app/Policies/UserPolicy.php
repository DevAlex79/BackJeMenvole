<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Vérifie si l'utilisateur peut créer un autre utilisateur (seuls les admins).
     */
    public function create(User $user): bool
    {
        return $user->Roles_id_role === 3; // Seuls les admins (role_id 3)
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->Roles_id_role === 3;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id_user === $model->id_user || $user->Roles_id_role === 3;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->Roles_id_role === 3 || $user->id_user === $model->id_user;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        //return $user->Roles_id_role === 3;
        return $user->Roles_id_role === 3 && $user->id_user === $model->id_user;
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, User $model): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, User $model): bool
    // {
    //     //
    // }
}
