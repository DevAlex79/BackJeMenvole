<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Lister tous les produits.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // $products = Product::all();

        // // Transformer le nom des ressources en "Articles"
        // return response()->json([
        //     'Articles' => $products
        // ], 200);

        $user = Auth::user(); // Récupérer l'utilisateur connecté

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        // Vérifier si l'utilisateur a le droit de voir des produits
        if ($user->Roles_id_role === 2) { // Si c'est un vendeur
            $products = Product::where('users_id_user', $user->id_user)
                ->with('category') // Charger la relation catégorie
                ->get();
        } elseif ($user->Roles_id_role === 3) { // Si c'est un admin
            $products = Product::with('category')->get(); // Voir tous les produits
        } else {
            return response()->json(['error' => 'Accès interdit'], 403);
        }
    
        return response()->json(['Articles' => $products], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Créer un nouveau produit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Ajoute un nouveau produit (réservé aux admins et vendeurs).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !Gate::allows('create', Product::class)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        // Validation des données de la requête
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categories_id_category' => 'required|exists:categories,id', // Exige une catégorie existante
        ]);

        // Vérifie si la catégorie existe bien
        $categoryExists = \App\Models\Category::where('id', $validated['categories_id_category'])->exists();
        if (!$categoryExists) {
            return response()->json(['error' => 'La catégorie spécifiée n\'existe pas.'], 422);
        }

        // Ajout automatique de l'ID du créateur (vendeur/admin)
        $validated['users_id_user'] = $user->id_user;

        // Création du produit avec toutes les données validées
        $product = Product::create($validated);
        // $product = Product::create([
        //     'title' => $validated['title'],
        //     'description' => $validated['description'],
        //     'price' => $validated['price'],
        //     'stock' => $validated['stock'],
        //     'categories_id_category' => $validated['categories_id_category'], // Ajout correct de la catégorie
        //     'users_id_user' => $user->id_user, // Ajout correct de l'ID de l'utilisateur]);

        return response()->json(['message' => 'Produit ajouté', 'product' => $product], 201);

    }

    /**
     * Affiche les détails d’un produit.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product, 200);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Afficher un produit spécifique.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        // $product = Product::with('category', 'user')->find($id);

        // if (!$product) {
        //     return response()->json(['message' => 'Produit non trouvé'], 404);
        // }

        // return response()->json($product, 200);
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        return response()->json(['Article' => $product], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Mettre à jour un produit existant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        if (!$user || !Gate::allows('update', $product)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $product->update($request->all());

        return response()->json(['message' => 'Produit mis à jour', 'product' => $product], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Supprimer un produit.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        if (!$user || !Gate::allows('delete', $product)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Produit supprimé'], 200);
    }

    public function updateStock(Request $request, $id)
    {
        $user = Auth::user();
        $product = Product::findOrFail($id);

        // Vérification d'autorisation via Policy
        if (!$user || !Gate::allows('updateStock', $product)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        // Validation des données de mise à jour
        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update(['stock' => $request->stock]);

        return response()->json(['message' => 'Stock mis à jour avec succès', 'product' => $product], 200);
    }

    
}
