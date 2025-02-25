<?php

//use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;

// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');

Route::get('/test', function () {
    return response()->json(['message' => 'CORS fonctionne !']);
});

Route::options('/{any}', function () {
    return response()->json([], 200);
})->where('any', '.*');


// Appliquer CORS à toutes les routes
Route::middleware([HandleCors::class])->group(function () {

    // Produits (Articles)
    Route::get('/articles', [ProductController::class, 'index']);        // Lister tous les articles
    Route::get('/articles/{id}', [ProductController::class, 'show']);    // Afficher un article spécifique

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);

    // Routes protégées (Admins & Vendeurs)
    Route::middleware(['jwt.auth:2,3'])->group(function () {
        Route::post('/articles', [ProductController::class, 'store']);       // Créer un article
        Route::put('/articles/{id}', [ProductController::class, 'update']);  // Modifier un article
        Route::delete('/articles/{id}', [ProductController::class, 'destroy']); // Supprimer un article
        Route::put('/articles/{id}/stock', [ProductController::class, 'updateStock']); // Modifier stock
    });

    // Debug / Tests API
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working!']);    
    });

    Route::get('/debug', function () {
        return response()->json(['status' => 'ok']);
    });

    // Gestion des rôles
    Route::get('/roles', [RoleController::class, 'index']);        
    Route::post('/roles', [RoleController::class, 'store']);       
    Route::get('/roles/{id}', [RoleController::class, 'show']);    
    Route::put('/roles/{id}', [RoleController::class, 'update']);  
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    // Authentification
    Route::post('/login', [AuthController::class, 'login'])->name('login'); // Connexion
    Route::post('/register', [RegisterController::class, 'register'])->name('register'); // Inscription
    Route::post('/logout', [AuthController::class, 'logout']); // Déconnexion
    Route::post('/refresh', [AuthController::class, 'refresh']); // Actualiser le token
    Route::get('/user-profile', [AuthController::class, 'userProfile']); // Profil utilisateur

    // Routes sécurisées JWT Auth
    Route::middleware(['jwt.auth'])->group(function () {

        // Utilisateurs
        Route::get('/users', [UserController::class, 'index']); // Lister les utilisateurs
        Route::put('/users/{id}', [UserController::class, 'update']); // Modifier un utilisateur
        Route::delete('/users/{id}', [UserController::class, 'destroy']); // Supprimer un utilisateur

        // Commandes
        Route::get('/orders', [OrderController::class, 'index']); 
        Route::post('/orders', [OrderController::class, 'store']); 
        Route::post('/orders/complete', [OrderController::class, 'completeOrder']);
        Route::put('/orders/{id}', [OrderController::class, 'update']); 
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
        Route::get('/orders/user/{id}', [OrderController::class, 'getUserOrders']);


        // Création utilisateur (Admin uniquement)
        Route::middleware(['jwt.auth:3'])->post('/admin/create-user', [UserController::class, 'createUser']);

        // Route pour récupérer toutes les commandes (accessible uniquement par les administrateurs)
        Route::middleware(['jwt.auth:3'])->get('/orders/all', [OrderController::class, 'getAllOrders']);

    });

});



// Route::get('/articles', [ProductController::class, 'index']);        // Lister tous les articles
// Route::post('/articles', [ProductController::class, 'store']);       // Créer un article
// Route::get('/articles/{id}', [ProductController::class, 'show']);    // Afficher un article spécifique
// Route::put('/articles/{id}', [ProductController::class, 'update']);  // Mettre à jour un article
// Route::delete('/articles/{id}', [ProductController::class, 'destroy']); // Supprimer un article
// Route::put('/articles/{id}/stock', [ProductController::class, 'updateStock']); // Mettre à jour le stock d'un article


// Route::get('/test', function () {
//     return response()->json(['message' => 'API is working!']);    
// });

// Route::get('/debug', function () {
//     return response()->json(['status' => 'ok']);
// });

// Route::get('/roles', [RoleController::class, 'index']);        // Lister tous les rôles
// Route::post('/roles', [RoleController::class, 'store']);       // Créer un rôle
// Route::get('/roles/{id}', [RoleController::class, 'show']);    // Afficher un rôle spécifique
// Route::put('/roles/{id}', [RoleController::class, 'update']);  // Mettre à jour un rôle
// Route::delete('/roles/{id}', [RoleController::class, 'destroy']); // Supprimer un rôle

// Route::post('/register', [RegisterController::class, 'register']); // Inscription

// // Routes pour les utilisateurs
// Route::get('/users', [UserController::class, 'index']); // Lister les utilisateurs
// Route::put('/users/{id}', [UserController::class, 'update']); // Modifier un utilisateur
// Route::delete('/users/{id}', [UserController::class, 'destroy']); // Supprimer un utilisateur

// // Routes pour les commandes
// Route::get('/orders', [OrderController::class, 'index']); // Lister toutes les commandes
// Route::post('/orders', [OrderController::class, 'store']); // Créer une commande
// Route::post('/orders/complete', [OrderController::class, 'completeOrder']); // Finalisation de commande
// Route::put('/orders/{id}', [OrderController::class, 'update']); // Modifier une commande
// Route::delete('/orders/{id}', [OrderController::class, 'destroy']); // Supprimer une commande


// Route::post('/login', [AuthController::class, 'login'])->name('login'); // Connexion
// Route::post('/register', [RegisterController::class, 'register'])->name('register'); // Inscription
// Route::post('/logout', [AuthController::class, 'logout']); // Deconnexion
// Route::post('/refresh', [AuthController::class, 'refresh']); // Actualiser le token
// Route::get('/user-profile', [AuthController::class, 'userProfile']); // Profil utilisateur

// Route::middleware('jwt.auth')->get('/user', function (Request $request) {
//     return response()->json([
//     'user' => Auth::guard('api')->user(),
//     'message' => 'Utilisateur authentifié avec succès'
// ]);
// });

// Route::middleware('auth:api')->post('/orders', [OrderController::class, 'store']);

// // Routes protégées : accès uniquement aux admins et vendeurs
// Route::middleware(['jwt.auth:2,3'])->group(function () {
//     Route::get('/admin/products', [ProductController::class, 'index']); // Lister produits
//     Route::post('/admin/products/store', [ProductController::class, 'store']); // Ajouter
//     Route::get('/admin/products/edit/{id}', [ProductController::class, 'edit']); // Voir un produit
//     Route::post('/admin/products/update/{id}', [ProductController::class, 'update']); // Modifier
//     Route::delete('/admin/products/delete/{id}', [ProductController::class, 'destroy']); // Supprimer
// });

// Route::middleware(['jwt.auth'])->group(function () {
//     Route::post('/admin/create-user', [UserController::class, 'createUser']);
// });






