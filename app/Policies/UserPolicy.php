<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\User;

class UserPolicy
{
    /**
     * Un admin peut créer n'importe quel type d'utilisateur.
     */
    public function create(User $user): bool
    {
        return RoleEnum::isAdmin($user->Roles_id_role);
    }

    /**
     * Seul un admin peut lister tous les utilisateurs.
     */
    public function viewAny(User $user): bool
    {
        return RoleEnum::isAdmin($user->Roles_id_role);
    }

    /**
     * Un utilisateur peut voir son propre profil ; un admin peut voir n'importe quel profil.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id_user === $model->id_user
            || RoleEnum::isAdmin($user->Roles_id_role);
    }

    /**
     * Un utilisateur peut modifier son propre profil ; un admin peut modifier n'importe quel profil.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id_user === $model->id_user
            || RoleEnum::isAdmin($user->Roles_id_role);
    }

    /**
     * Règle de suppression :
     *   - Un admin peut supprimer n'importe quel utilisateur (y compris d'autres comptes).
     *   - Un utilisateur peut supprimer son propre compte.
     *
     * CORRECTION : l'ancienne version utilisait && (ET logique), ce qui rendait
     * impossible la suppression d'un autre compte par l'admin. Remplacé par || (OU).
     */
    public function delete(User $user, User $model): bool
    {
        return RoleEnum::isAdmin($user->Roles_id_role)
            || $user->id_user === $model->id_user;
    }
}
