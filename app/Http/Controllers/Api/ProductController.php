<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Liste les produits accessibles à l'utilisateur connecté (back-office).
     *
     * - Vendeur (RoleEnum::Vendeur)        → uniquement ses propres produits
     * - Administrateur (RoleEnum::Admin)   → tous les produits
     * - Autres rôles                       → 403 Forbidden
     *
     * Les résultats sont paginés (20 par page) pour éviter les surcharges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Route publique sans token : l'utilisateur n'est pas authentifié
        if (!$user) {
            return response()->json(['error' => 'Authentification requise'], 401);
        }

        if ($user->Roles_id_role === RoleEnum::Vendeur->value) {
            // Un vendeur ne voit que ses propres produits
            $products = Product::where('users_id_user', $user->id_user)
                ->with('category')
                ->paginate(20);

        } elseif ($user->Roles_id_role === RoleEnum::Administrateur->value) {
            // Un admin voit tous les produits
            $products = Product::with('category')->paginate(20);

        } else {
            return response()->json(['error' => 'Accès interdit'], 403);
        }

        // Utilisation de la Resource pour transformer la réponse (title → name, etc.)
        return ProductResource::collection($products)->response();
    }

    /**
     * Crée un nouveau produit (réservé aux vendeurs et admins).
     *
     * La vérification d'autorisation passe par la ProductPolicy::create()
     * enregistrée dans AuthServiceProvider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!Gate::allows('create', Product::class)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'price'                  => 'required|numeric|min:0',
            'stock'                  => 'required|integer|min:0',
            'categories_id_category' => 'required|exists:categories,id',
        ]);

        // Assignation automatique du créateur
        $validated['users_id_user'] = $user->id_user;

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produit ajouté',
            'product' => new ProductResource($product),
        ], 201);
    }

    /**
     * Affiche un produit spécifique (accessible publiquement).
     *
     * @param  int  $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        return response()->json(['Article' => new ProductResource($product)], 200);
    }

    /**
     * Met à jour un produit existant.
     *
     * Seuls les champs explicitement autorisés sont mis à jour (via only()).
     * Cela évite qu'un vendeur puisse modifier users_id_user ou d'autres
     * champs sensibles en envoyant des données non prévues.
     *
     * La vérification passe par ProductPolicy::update().
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        if (!Gate::allows('update', $product)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'title'                  => 'sometimes|string|max:255',
            'description'            => 'sometimes|nullable|string',
            'price'                  => 'sometimes|numeric|min:0',
            'stock'                  => 'sometimes|integer|min:0',
            'categories_id_category' => 'sometimes|exists:categories,id',
            'image'                  => 'sometimes|nullable|string',
            'alt'                    => 'sometimes|nullable|string',
        ]);

        // Mise à jour uniquement avec les champs validés (pas de $request->all())
        $product->update($validated);

        return response()->json([
            'message' => 'Produit mis à jour',
            'product' => new ProductResource($product),
        ], 200);
    }

    /**
     * Supprime un produit.
     * La vérification passe par ProductPolicy::delete().
     *
     * @param  int  $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if (!Gate::allows('delete', $product)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Produit supprimé'], 200);
    }

    /**
     * Met à jour uniquement le stock d'un produit.
     * La vérification passe par ProductPolicy::updateStock().
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int                       $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStock(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if (!Gate::allows('updateStock', $product)) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update(['stock' => $request->stock]);

        return response()->json([
            'message' => 'Stock mis à jour avec succès',
            'product' => new ProductResource($product),
        ], 200);
    }
}
