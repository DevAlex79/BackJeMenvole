<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;

/**
 * Règles d'autorisation sur les comptes utilisateurs.
 *
 * Auto-découverte Laravel 11 : App\Models\User => App\Policies\UserPolicy.
 */
class UserPolicy
{
    /**
     * Seul un administrateur peut créer un compte via l'API d'administration.
     */
    public function create(User $user): bool
    {
        return RoleEnum::isAdmin((int) $user->Roles_id_role);
    }

    /**
     * Seul un administrateur peut lister tous les comptes.
     */
    public function viewAny(User $user): bool
    {
        return RoleEnum::isAdmin((int) $user->Roles_id_role);
    }

    /**
     * Consulter un profil : son propre compte, ou n'importe lequel si admin.
     */
    public function view(User $user, User $model): bool
    {
        return $this->selfOrAdmin($user, $model);
    }

    /**
     * Modifier un profil : son propre compte, ou n'importe lequel si admin.
     */
    public function update(User $user, User $model): bool
    {
        return $this->selfOrAdmin($user, $model);
    }

    /**
     * Supprimer un compte : le sien, ou n'importe lequel si admin.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->selfOrAdmin($user, $model);
    }

    private function selfOrAdmin(User $user, User $model): bool
    {
        return (int) $user->id_user === (int) $model->id_user
            || RoleEnum::isAdmin((int) $user->Roles_id_role);
    }
}
