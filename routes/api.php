<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;


// Route pour obtenir tous les produits
//Route::get('/products', [ProductController::class, 'index']); // GET pour lister tous les produits

// Route pour créer un nouveau produit
//Route::post('/products', [ProductController::class, 'store']); // POST pour créer un produit

// Route pour obtenir un produit spécifique
//Route::get('/products/{id}', [ProductController::class, 'show']); // GET pour afficher un produit

// Route pour mettre à jour un produit existant
//Route::put('/products/{id}', [ProductController::class, 'update']); // PUT pour mettre à jour un produit entier
//Route::patch('/products/{id}', [ProductController::class, 'update']); // PATCH pour mettre à jour partiellement un produit

// Route pour supprimer un produit
//Route::delete('/products/{id}', [ProductController::class, 'destroy']); // DELETE pour supprimer un produit

Route::get('/articles', [ProductController::class, 'index']);        // Lister tous les articles
Route::post('/articles', [ProductController::class, 'store']);       // Créer un article
Route::get('/articles/{id}', [ProductController::class, 'show']);    // Afficher un article spécifique
Route::put('/articles/{id}', [ProductController::class, 'update']);  // Mettre à jour un article
Route::delete('/articles/{id}', [ProductController::class, 'destroy']); // Supprimer un article
Route::put('/articles/{id}/stock', [ProductController::class, 'updateStock']); // Mettre à jour le stock d'un article


Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);    
});

Route::get('/debug', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/roles', [RoleController::class, 'index']);        // Lister tous les rôles
Route::post('/roles', [RoleController::class, 'store']);       // Créer un rôle
Route::get('/roles/{id}', [RoleController::class, 'show']);    // Afficher un rôle spécifique
Route::put('/roles/{id}', [RoleController::class, 'update']);  // Mettre à jour un rôle
Route::delete('/roles/{id}', [RoleController::class, 'destroy']); // Supprimer un rôle

Route::post('/register', [RegisterController::class, 'register']); // Inscription

// Routes pour les utilisateurs
Route::get('/users', [UserController::class, 'index']); // Lister les utilisateurs
Route::put('/users/{id}', [UserController::class, 'update']); // Modifier un utilisateur
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Supprimer un utilisateur

// Routes pour les commandes
Route::get('/orders', [OrderController::class, 'index']); // Lister toutes les commandes
Route::post('/orders', [OrderController::class, 'store']); // Créer une commande
Route::post('/orders/complete', [OrderController::class, 'completeOrder']); // Finalisation de commande
Route::put('/orders/{id}', [OrderController::class, 'update']); // Modifier une commande
Route::delete('/orders/{id}', [OrderController::class, 'destroy']); // Supprimer une commande

Route::post('/login', [AuthController::class, 'login']); // Connexion
Route::post('/register', [AuthController::class, 'register']); // Inscription
Route::post('/logout', [AuthController::class, 'logout']); // Deconnexion
Route::post('/refresh', [AuthController::class, 'refresh']); // Actualiser le token
Route::get('/user-profile', [AuthController::class, 'userProfile']); // Profil utilisateur

// Route sécurisée pour récupérer les infos utilisateur
// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return response()->json([
//         'user' => $request->user()
//     ]);
// });
Route::middleware('jwt.auth')->get('/user', function (Request $request) {
   // return response()->json(['user' => Auth::user()]);
    return response()->json([
    'user' => Auth::guard('api')->user(),
    'message' => 'Utilisateur authentifié avec succès'
]);
});


Route::middleware('auth:api')->post('/orders', [OrderController::class, 'store']);



