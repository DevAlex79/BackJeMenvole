<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Product;
use App\Policies\UserPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Définition des policies.
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Product::class => ProductPolicy::class,
    ];
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('create-user', [UserPolicy::class, 'create']);
        Gate::define('manage-products', [ProductPolicy::class, 'manage']);

        // Ajout d'un log pour vérifier si Laravel charge bien AuthServiceProvider
        //Log::info("AuthServiceProvider chargé.");
        // Vérifier que les Gates sont bien enregistrés
        //$this->registerPolicies();

        // Vérification de l'autorisation des admins pour gérer les utilisateurs
        // Gate::define('manage-users', function (?User $user) {
        //     Log::info("Gate 'manage-users' testé.", ['user_id' => $user->id_user, 'role' => $user->Roles_id_role]);
        //     return $user && $user->Roles_id_role === 3; // Vérifie que l'utilisateur est bien connecté et admin
        // });

        // Gate::define('manage-users', function (User $user) {
        //     Log::info("Vérification du Gate 'manage-users'", ['user_id' => $user->id_user, 'role' => $user->Roles_id_role]);
        //     return $user->Roles_id_role === 3;
        // });
        
    
        // Vérification pour la gestion des produits
        // Gate::define('manage-products', function (User $user) {
        //     return in_array($user->Roles_id_role, [2, 3]); // Rôles vendeur et admin
        // });

        // Log::info("Gates enregistrés avec succès.");



        // $this->registerPolicies();

        // Log::info("AuthServiceProvider chargé");

        // Gate::define('manage-users', function (User $user) {
        //     $user->loadMissing('role'); // Force le chargement du rôle
        //     Log::info("Vérification du Gate 'manage-users'", [
        //         'user_id' => $user->id_user,
        //         'role_id' => $user->role ? $user->role->id_role : 'Aucun rôle'
        //     ]);
        
        //     return $user->Roles_id_role === 3;
        //     if (!$user || !$user->role) {
        //         Log::warning("Tentative d'accès à 'manage-users' sans rôle défini.", ['user_id' => $user->id_user ?? 'Non authentifié']);
        //         return false;
        //     }

        //     Log::info("Vérification du Gate 'manage-users'", [
        //         'user_id' => $user->id_user,
        //         'role_id' => $user->role->id_role, // Utilisation de la relation role()
        //     ]);

        //     return $user->role->id_role === 3;
        // });

        // Gate::define('manage-products', function (User $user) {
        //     return $user->role->id_role === 2 || $user->role->id_role === 3;
        // });
    }
}
