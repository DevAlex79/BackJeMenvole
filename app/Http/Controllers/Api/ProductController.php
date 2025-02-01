<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    public function index()
    {
        // $products = Product::with('category', 'user')->get();
        // return response()->json($products, 200);
        $products = Product::all();

        // Transformer le nom des ressources en "Articles"
        return response()->json([
            'Articles' => $products
        ], 200);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            // 'name' => 'required|string|max:255',
            // 'description' => 'nullable|string',
            // 'price' => 'required|numeric|min:0',
            // 'category_id' => 'required|exists:categories,id',
            // 'user_id' => 'required|exists:users,id'
            'title' => 'required|string|max:255', // Aligner avec le champ title
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
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
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $validated = $request->validate([
            // 'name' => 'sometimes|required|string|max:255',
            // 'description' => 'nullable|string',
            // 'price' => 'sometimes|required|numeric|min:0',
            // 'category_id' => 'sometimes|required|exists:categories,id',
            // 'user_id' => 'sometimes|required|exists:users,id'
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ]);

        $product->update($validated);

        //return response()->json($product, 200);
        return response()->json(['Article' => $product], 200);
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
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès'], 200);
    }

    public function updateStock(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Produit introuvable'], 404);
        }

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
