<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderCompletedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Finalise une commande.
     */
    public function completeOrder(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id_user',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:20',
            'cart' => 'required|array',
            'cart.*.id_product' => 'required|exists:products,id_product',
            'cart.*.quantity' => 'required|integer|min:1',
        ],[
            'user_id.required' => "L'identifiant de l'utilisateur est requis.",
            'user_id.exists' => "L'utilisateur spécifié n'existe pas.",
            'total_price.required' => "Le prix total est requis.",
            'total_price.numeric' => "Le prix total doit être un nombre.",
            'status.required' => "Le statut est requis.",
            'status.string' => "Le statut doit être une chaîne de caractères."
        ]);

         // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Récupérer les données validées
        $validated = $validator->validated();

        // Vérifier que l'utilisateur existe
        $user = User::where('id_user', $validated['id_user'])->first();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Vérifier le stock des produits
        foreach ($validated['cart'] as $item) {
            $product = Product::find($item['id_product']);
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'error' => "Stock insuffisant pour le produit : {$product->name}. Disponible : {$product->stock}, demandé : {$item['quantity']}."
                ], 400);
            }
        }

        // Décrémenter le stock des produits commandés
        foreach ($validated['cart'] as $item) {
            $product = Product::find($item['id_product']);
            $product->stock -= $item['quantity'];
            $product->save();
        }

        // Créer la commande
        $order = Order::create([
            'users_id_user' => $validated['user_id'],
            'total_price' => $validated['total_price'],
            'status' => $validated['status'],
            'shipment_type' => $request->shipmentType,
            'shipment_price' => $request->shipmentPrice,
            'cart' => json_encode($validated['cart']),

        ]);

        // Récupérer l'utilisateur lié et envoyer la notification
        $order->user->notify(new OrderCompletedNotification($order));

        return response()->json(['message' => 'Commande finalisée avec succès', 'order' => $order], 201);
    }

    public function index()
    {
        $user = Auth::user(); // Récupérer l'utilisateur connecté

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }
    
        // Filtrer les commandes par utilisateur connecté
        $orders = Order::where('users_id_user', $user->id_user)->with('user')->get();
    
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Aucune commande trouvée'], 404);
        }
    
        return response()->json($orders, 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Commande introuvable'], 404);
        }

        $validated = $request->validate([
            'total_price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|string|max:20',
        ]);

        $order->update($validated);

        return response()->json(['message' => 'Commande mise à jour avec succès', 'order' => $order], 200);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        $user = Auth::user();

        if (!$order) {
            return response()->json(['error' => 'Commande non trouvée'], 404);
        }
    
        // Seuls les administrateurs ou les utilisateurs avec commande en attente peuvent annuler
        if ($user->Roles_id_role === 3 || ($user->id_user === $order->users_id_user && $order->status === 'en attente')) {
            $order->delete();
            return response()->json(['message' => 'Commande annulée avec succès'], 200);
        } else {
            return response()->json(['error' => 'Vous ne pouvez pas annuler cette commande'], 403);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $validator = Validator::make($request->all(), [
            // 'user_id' => 'required|exists:users,id_user',
            // 'total_price' => 'required|numeric|min:0',
            // 'status' => 'required|string|max:20',
            //'user_id' => 'required|exists:users,id',
            'id_user' => 'required|exists:users,id_user',
            'cart' => 'required|array|min:1',
            'cart.*.title' => 'required|string',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.unitPrice' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:en attente,confirmée,expédiée,livrée',
            'shipmentType' => 'required|string',
            'shipmentPrice' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que l'utilisateur existe
        $user = User::find($request->id_user);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        Log::info('Données reçues pour création de commande', $request->all());

         // Vérifier le stock disponible pour chaque produit
        foreach ($request->cart as $item) {
            $product = Product::where('title', $item['title'])->first();

            if (!$product) {
                return response()->json(['error' => "Le produit '{$item['title']}' n'existe pas."], 404);
            }

            if ($product->stock < $item['quantity']) {
                return response()->json(['error' => "Stock insuffisant pour le produit '{$item['title']}'."], 400);
            }
        }

        // Créer la commande
        $order = Order::create([
            // 'users_id_user' => $request->user_id,
            // 'total_price' => $request->total_price,
            // 'status' => $request->status,
            //'user_id' => $request->user_id,
            'users_id_user' => $request->id_user,
            'cart' => json_encode($request->cart),
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);

        // Décrémenter le stock des produits commandés
        foreach ($request->cart as $item) {
            $product = Product::where('title', $item['title'])->first();

            if ($product) {
                $product->decrement('stock', $item['quantity']);
            }
        }

        // Notifier l'utilisateur
        $order->user->notify(new OrderCompletedNotification($order));

        return response()->json(['message' => 'Commande créée avec succès', 'order' => $order], 201);
    }

    public function getUserOrders($id)
    {
        // Vérifie si l'utilisateur est bien authentifié
        $user = Auth::user();
        if (!$user || $user->id_user != $id) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }
    
        // Récupère les commandes de l'utilisateur connecté
        $orders = Order::where('users_id_user', $id)->orderBy('created_at', 'desc')->get();
    
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Aucune commande trouvée.'], 404);
        }
    
        return response()->json($orders, 200);
    }
    
    public function getAllOrders()
{
    try {
        // Vérification du rôle de l'utilisateur connecté
        $user = Auth::user();
        if (!$user || $user->Roles_id_role !== 3) {
            return response()->json(['error' => 'Accès refusé'], 403);
        }

        // Récupération de toutes les commandes
        $orders = \App\Models\Order::with('user', 'items')->get();

        return response()->json($orders, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de la récupération des commandes'], 500);
    }
}



}
