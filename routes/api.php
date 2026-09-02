<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Je m'envole
|--------------------------------------------------------------------------
|
| Préfixe /api (bootstrap/app.php) et CORS appliqués globalement.
|
| Rôles (App\Enums\RoleEnum) :
|   1 = Client          → lecture publique du catalogue, gestion de ses commandes
|   2 = Vendeur         → gestion de ses propres produits
|   3 = Administrateur  → accès total
|
| Middlewares :
|   'jwt.auth'   → authentification JWT (tymon/jwt-auth)
|   'role:x,y'   → l'utilisateur doit avoir l'un des rôles listés
|
*/

// -----------------------------------------------------------------------------
// ROUTES PUBLIQUES
// -----------------------------------------------------------------------------
Route::get('/articles', [ProductController::class, 'index']);
Route::get('/articles/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/roles', [RoleController::class, 'index']);
Route::get('/roles/{id}', [RoleController::class, 'show']);

// Formulaire de contact — rate limité pour éviter le spam / mail-bombing.
Route::post('/messages', [MessageController::class, 'store'])->middleware('throttle:5,1');

// -----------------------------------------------------------------------------
// AUTHENTIFICATION — max 10 tentatives/minute (anti brute-force)
// -----------------------------------------------------------------------------
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/register', [RegisterController::class, 'register'])->name('register');
});

// -----------------------------------------------------------------------------
// ROUTES AUTHENTIFIÉES (tout utilisateur connecté)
// -----------------------------------------------------------------------------
Route::middleware('jwt.auth')->group(function () {

    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);

    // Utilisateurs
    // - index : admins uniquement (voir aussi UserPolicy::viewAny)
    // - show/update/destroy : le propriétaire OU un admin (contrôlé par UserPolicy)
    Route::get('/users', [UserController::class, 'index'])->middleware('role:3');
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/admin/create-user', [UserController::class, 'createUser'])->middleware('role:3');

    // Commandes — routes statiques AVANT les routes paramétrées
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::middleware('role:3')->group(function () {
        Route::get('/orders/archived', [OrderController::class, 'getArchivedOrders']);
        Route::get('/orders/all', [OrderController::class, 'getAllOrders']);
    });

    Route::get('/orders/user/{id}', [OrderController::class, 'getUserOrders']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
});

// -----------------------------------------------------------------------------
// GESTION DU CATALOGUE — Vendeurs + Administrateurs
// (ProductPolicy vérifie en plus la propriété produit par produit)
// -----------------------------------------------------------------------------
Route::middleware(['jwt.auth', 'role:2,3'])->group(function () {
    Route::post('/articles', [ProductController::class, 'store']);
    Route::put('/articles/{id}', [ProductController::class, 'update']);
    Route::delete('/articles/{id}', [ProductController::class, 'destroy']);
    Route::put('/articles/{id}/stock', [ProductController::class, 'updateStock']);
});

// -----------------------------------------------------------------------------
// GESTION DES RÔLES — Administrateurs uniquement
// -----------------------------------------------------------------------------
Route::middleware(['jwt.auth', 'role:3'])->group(function () {
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
});
