<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Vérifie si l'utilisateur peut gérer les produits (admins et vendeurs).
     * Utilisé par le Gate 'manage-products'.
     */
    public function manage(User $user): bool
    {
        return RoleEnum::canManageProducts($user->Roles_id_role);
    }

    /**
     * Seuls les vendeurs et admins peuvent lister les produits (back-office).
     * Les clients accèdent aux produits via la route publique /articles.
     */
    public function viewAny(User $user): bool
    {
        return RoleEnum::canManageProducts($user->Roles_id_role);
    }

    /**
     * Règle de consultation d'un produit :
     *   - Vendeur  → uniquement ses propres produits
     *   - Admin    → tous les produits
     *
     * CORRECTION : l'ancienne version comparait $product->user_id et $user->id
     * (champs Laravel par défaut inexistants ici). Les bonnes FK sont
     * $product->users_id_user et $user->id_user.
     */
    public function view(User $user, Product $product): bool
    {
        if ($user->Roles_id_role === RoleEnum::Vendeur->value) {
            return $product->users_id_user === $user->id_user;
        }

        if ($user->Roles_id_role === RoleEnum::Administrateur->value) {
            return true;
        }

        return false;
    }

    /**
     * Seuls les vendeurs et admins peuvent créer un produit.
     */
    public function create(User $user): bool
    {
        return RoleEnum::canManageProducts($user->Roles_id_role);
    }

    /**
     * Règle de modification d'un produit :
     *   - Vendeur  → uniquement ses propres produits
     *   - Admin    → tous les produits
     *
     * CORRECTION : même correction de FK que view().
     */
    public function update(User $user, Product $product): bool
    {
        if ($user->Roles_id_role === RoleEnum::Vendeur->value) {
            return $product->users_id_user === $user->id_user;
        }

        if ($user->Roles_id_role === RoleEnum::Administrateur->value) {
            return true;
        }

        return false;
    }

    /**
     * Règle de mise à jour du stock :
     *   - Vendeur  → uniquement ses propres produits
     *   - Admin    → tous les produits
     */
    public function updateStock(User $user, Product $product): bool
    {
        if ($user->Roles_id_role === RoleEnum::Vendeur->value) {
            return $product->users_id_user === $user->id_user;
        }

        if ($user->Roles_id_role === RoleEnum::Administrateur->value) {
            return true;
        }

        return false;
    }

    /**
     * Règle de suppression d'un produit :
     *   - Vendeur  → uniquement ses propres produits
     *   - Admin    → tous les produits
     *
     * CORRECTION : même correction de FK que view().
     */
    public function delete(User $user, Product $product): bool
    {
        if ($user->Roles_id_role === RoleEnum::Vendeur->value) {
            return $product->users_id_user === $user->id_user;
        }

        if ($user->Roles_id_role === RoleEnum::Administrateur->value) {
            return true;
        }

        return false;
    }
}
