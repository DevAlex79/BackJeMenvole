<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Vérifie si l'utilisateur peut gérer les produits (admins et vendeurs).
     */
    public function manage(User $user) :bool
    {
        return in_array($user->Roles_id_role, [2, 3]); // Vendeur (2) & Admin (3)
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->Roles_id_role, [2, 3]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        //return in_array($user->Roles_id_role, [2, 3]);
        // Check if the user has permission to view the product
        if ($user->Roles_id_role === 2) {
            // Only allow sellers to view products they own
            return $product->user_id === $user->id;
        } elseif ($user->Roles_id_role === 3) {
            // Allow admins to view any product
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        //return in_array($user->Roles_id_role, [2, 3]);
        if ($user->Roles_id_role === 2) {
            // Only allow sellers to update products they own
            return $product->user_id === $user->id;
        } elseif ($user->Roles_id_role === 3) {
            // Allow admins to update any product
            return true;
        } else {
            return false;
        }
    }

    public function updateStock(User $user, Product $product): bool
    {
        // Un vendeur (role = 2) peut modifier le stock SEULEMENT pour ses propres produits
        if ($user->Roles_id_role === 2) {
            return $product->users_id_user === $user->id_user;
        }

        // Un administrateur (role = 3) peut modifier le stock de n'importe quel produit
        if ($user->Roles_id_role === 3) {
            return true;
        }

        return false; // Tout autre utilisateur est refusé
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        //return in_array($user->Roles_id_role, [2, 3]);
        if ($user->Roles_id_role === 2) {
            // Only allow sellers to delete products they own
            return $product->user_id === $user->id;
        } elseif ($user->Roles_id_role === 3) {
            // Allow admins to delete any product
            return true;
        } else {
            return false;
        }
    }

    /**
     * Vérifie si l'utilisateur peut créer un produit.
     */
    public function create(User $user): bool
    {
        return in_array($user->Roles_id_role, [2, 3]); // Seuls les vendeurs et admins peuvent créer
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Product $product): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Product $product): bool
    // {
    //     //
    // }
}
