<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Notifications\OrderCompletedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Finalise une commande.
     */
    public function completeOrder(Request $request)
    {
        // Valider les données d'entrée

        //$validated = $request->validate([
        //     'user_id' => 'required|exists:users,id_user',
        //     'total_price' => 'required|numeric|min:0',
        //     'status' => 'required|string|max:20',
        // ]);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id_user',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:20',
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
        $user = User::find($validated['user_id']);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Créer la commande
        $order = Order::create([
            //'user_id' => $request->user_id,
            //'amount' => $request->amount,
            'users_id_user' => $validated['user_id'],
            'total_price' => $validated['total_price'],
            'status' => $validated['status'],
        ]);

        // Récupérer l'utilisateur lié et envoyer la notification
        $order->user->notify(new OrderCompletedNotification($order));

        return response()->json(['message' => 'Commande finalisée avec succès', 'order' => $order], 201);
    }

    public function index()
    {
        $orders = Order::with('user')->get();

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

        if (!$order) {
            return response()->json(['error' => 'Commande introuvable'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Commande supprimée avec succès'], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id_user',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que l'utilisateur existe
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Créer la commande
        $order = Order::create([
            'users_id_user' => $request->user_id,
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);

        // Notifier l'utilisateur
        $order->user->notify(new OrderCompletedNotification($order));

        return response()->json(['message' => 'Commande créée avec succès', 'order' => $order], 201);
    }




}
