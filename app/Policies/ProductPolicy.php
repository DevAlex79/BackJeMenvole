<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\User;

/**
 * Règles d'autorisation sur les produits.
 *
 * Principe commun à view / update / updateStock / delete :
 *   - un vendeur n'agit que sur SES produits (users_id_user) ;
 *   - un administrateur agit sur tous les produits ;
 *   - tout autre rôle est refusé.
 *
 * Auto-découverte Laravel 11 : App\Models\Product => App\Policies\ProductPolicy.
 */
class ProductPolicy
{
    /**
     * L'utilisateur peut-il agir sur ce produit précis ?
     */
    private function owns(User $user, Product $product): bool
    {
        if ((int) $user->Roles_id_role === RoleEnum::Administrateur->value) {
            return true;
        }

        return (int) $user->Roles_id_role === RoleEnum::Vendeur->value
            && (int) $product->users_id_user === (int) $user->id_user;
    }

    /**
     * Lister le back-office produits (vendeurs et admins).
     */
    public function viewAny(User $user): bool
    {
        return RoleEnum::canManageProducts((int) $user->Roles_id_role);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    /**
     * Créer un produit (vendeurs et admins).
     */
    public function create(User $user): bool
    {
        return RoleEnum::canManageProducts((int) $user->Roles_id_role);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function updateStock(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->owns($user, $product);
    }
}
