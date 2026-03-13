<?php

namespace App\Enums;

/**
 * Enum représentant les rôles disponibles dans l'application.
 *
 * Utilisation : RoleEnum::Administrateur->value  → 3
 *               RoleEnum::from(3)                → RoleEnum::Administrateur
 *
 * Centralise les identifiants de rôle pour éviter les "magic numbers"
 * éparpillés dans le code (1, 2, 3).
 */
enum RoleEnum: int
{
    /** Utilisateur standard — peut parcourir et commander des produits. */
    case Client = 1;

    /** Vendeur — peut créer et gérer ses propres produits. */
    case Vendeur = 2;

    /** Administrateur — accès complet à toute l'application. */
    case Administrateur = 3;

    /**
     * Retourne le libellé lisible du rôle.
     */
    public function label(): string
    {
        return match($this) {
            RoleEnum::Client         => 'Client',
            RoleEnum::Vendeur        => 'Vendeur',
            RoleEnum::Administrateur => 'Administrateur',
        };
    }

    /**
     * Vérifie si le rôle donné (int) correspond à un admin.
     */
    public static function isAdmin(int $roleId): bool
    {
        return $roleId === self::Administrateur->value;
    }

    /**
     * Vérifie si le rôle donné (int) est vendeur ou admin.
     */
    public static function canManageProducts(int $roleId): bool
    {
        return in_array($roleId, [self::Vendeur->value, self::Administrateur->value]);
    }
}
