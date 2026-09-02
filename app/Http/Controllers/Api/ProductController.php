<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Gestion des produits (articles du catalogue).
 *
 * Lecture : publique (catalogue).
 * Écriture : vendeurs et administrateurs, avec vérification de propriété
 *            produit par produit via App\Policies\ProductPolicy
 *            (auto-découverte des policies en Laravel 11).
 */
class ProductController extends Controller
{
    /**
     * Liste les produits, paginés (20/page) et transformés par ProductResource.
     *
     * Le périmètre dépend de l'appelant :
     *   - visiteur anonyme ou client  → catalogue complet (vitrine) ;
     *   - vendeur                      → uniquement ses propres produits ;
     *   - administrateur               → tous les produits.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Product::query()->with('category');

        // Un vendeur ne pilote que son propre stock depuis le back-office.
        if ($user && (int) $user->Roles_id_role === RoleEnum::Vendeur->value) {
            $query->where('users_id_user', $user->id_user);
        }
        // Client / anonyme / admin : catalogue complet (aucun filtre).

        return ProductResource::collection($query->paginate(20))->response();
    }

    /**
     * Crée un produit. Le créateur (vendeur/admin) en devient propriétaire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Product::class);

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'price'                  => 'required|numeric|min:0',
            'stock'                  => 'required|integer|min:0',
            'categories_id_category' => 'required|exists:categories,id',
            'image'                  => 'nullable|string',
            'alt'                    => 'nullable|string',
        ]);

        // Le propriétaire est imposé côté serveur, jamais fourni par le client.
        $validated['users_id_user'] = Auth::user()->id_user;

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produit ajouté',
            'product' => new ProductResource($product->load('category')),
        ], 201);
    }

    /**
     * Affiche un produit (accessible publiquement).
     *
     * @param  string  $id  Identifiant du produit (id_product)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);

        if (! $product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        // Clé « Article » conservée pour compatibilité avec le frontend.
        return response()->json(['Article' => new ProductResource($product)], 200);
    }

    /**
     * Met à jour un produit. Seuls les champs listés sont modifiables
     * (jamais users_id_user : pas de transfert de propriété via l'API).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        Gate::authorize('update', $product);

        $validated = $request->validate([
            'title'                  => 'sometimes|string|max:255',
            'description'            => 'sometimes|nullable|string',
            'price'                  => 'sometimes|numeric|min:0',
            'stock'                  => 'sometimes|integer|min:0',
            'categories_id_category' => 'sometimes|exists:categories,id',
            'image'                  => 'sometimes|nullable|string',
            'alt'                    => 'sometimes|nullable|string',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Produit mis à jour',
            'product' => new ProductResource($product->load('category')),
        ], 200);
    }

    /**
     * Supprime un produit.
     *
     * @param  string  $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        Gate::authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Produit supprimé'], 200);
    }

    /**
     * Met à jour uniquement le stock d'un produit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $id  Identifiant du produit
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStock(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        Gate::authorize('updateStock', $product);

        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Stock mis à jour avec succès',
            'product' => new ProductResource($product->load('category')),
        ], 200);
    }
}
