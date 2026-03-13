<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Je m'envole
|--------------------------------------------------------------------------
|
| Toutes les routes sont préfixées par /api (voir bootstrap/app.php).
| Middleware CORS appliqué globalement.
|
| Rôles (RoleEnum) :
|   1 = Client       → accès lecture publique
|   2 = Vendeur      → gestion de ses propres produits
|   3 = Administrateur → accès total
|
*/

Route::middleware([HandleCors::class])->group(function () {

    // -------------------------------------------------------------------------
    // ROUTES PUBLIQUES
    // -------------------------------------------------------------------------

    // Catalogue produits (lecture seule, sans authentification)
    Route::get('/articles', [ProductController::class, 'index']);
    Route::get('/articles/{id}', [ProductController::class, 'show']);

    // Catégories (lecture seule)
    Route::get('/categories', [CategoryController::class, 'index']);

    // Rôles (lecture seule — utilisé par le formulaire d'inscription admin)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);

    // Formulaire de contact (protection XSS assurée dans MessageController)
    Route::post('/messages', [MessageController::class, 'store']);

    // -------------------------------------------------------------------------
    // AUTHENTIFICATION
    // Rate limiting : max 10 tentatives par minute pour prévenir le brute-force.
    // -------------------------------------------------------------------------
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [RegisterController::class, 'register'])->name('register');
    });

    // -------------------------------------------------------------------------
    // ROUTES PROTÉGÉES PAR JWT (tout utilisateur authentifié)
    // -------------------------------------------------------------------------
    Route::middleware(['jwt.auth'])->group(function () {

        // Authentification
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);

        // Profil utilisateur
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Commandes (routes statiques AVANT les routes paramétrées pour éviter les conflits)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::post('/orders/complete', [OrderController::class, 'completeOrder']);

        // Routes admin pour les commandes (jwt.auth:3 imbriqué)
        Route::middleware(['jwt.auth:3'])->group(function () {
            Route::get('/orders/archived', [OrderController::class, 'getArchivedOrders']);
            Route::get('/orders/all', [OrderController::class, 'getAllOrders']);
        });

        // Routes paramétrées après les routes statiques
        Route::get('/orders/user/{id}', [OrderController::class, 'getUserOrders']);
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

        // Création d'utilisateur par un admin
        Route::middleware(['jwt.auth:3'])->post('/admin/create-user', [UserController::class, 'createUser']);
    });

    // -------------------------------------------------------------------------
    // ROUTES PROTÉGÉES PAR JWT (Vendeurs + Administrateurs uniquement)
    // -------------------------------------------------------------------------
    Route::middleware(['jwt.auth:2,3'])->group(function () {
        Route::post('/articles', [ProductController::class, 'store']);
        Route::put('/articles/{id}', [ProductController::class, 'update']);
        Route::delete('/articles/{id}', [ProductController::class, 'destroy']);
        Route::put('/articles/{id}/stock', [ProductController::class, 'updateStock']);

        // Gestion des rôles (écriture réservée aux admins/vendeurs)
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    });

});
