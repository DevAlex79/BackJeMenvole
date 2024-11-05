<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

// Route pour obtenir tous les produits
Route::get('/products', [ProductController::class, 'index']); // GET pour lister tous les produits

// Route pour créer un nouveau produit
Route::post('/products', [ProductController::class, 'store']); // POST pour créer un produit

// Route pour obtenir un produit spécifique
Route::get('/products/{id}', [ProductController::class, 'show']); // GET pour afficher un produit

// Route pour mettre à jour un produit existant
Route::put('/products/{id}', [ProductController::class, 'update']); // PUT pour mettre à jour un produit entier
Route::patch('/products/{id}', [ProductController::class, 'update']); // PATCH pour mettre à jour partiellement un produit

// Route pour supprimer un produit
Route::delete('/products/{id}', [ProductController::class, 'destroy']); // DELETE pour supprimer un produit
