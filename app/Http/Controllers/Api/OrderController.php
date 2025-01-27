<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Notifications\OrderCompletedNotification;
use App\Models\User;

class OrderController extends Controller
{
    /**
     * Finalise une commande.
     */
    public function completeOrder(Request $request)
    {
        // Valider les données d'entrée
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id_user',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|max:20',
        ]);

        // Créer la commande
        $order = Order::create([
            //'user_id' => $request->user_id,
            //'amount' => $request->amount,
            'users_id_user' => $validated['user_id'],
            'total_price' => $validated['total_price'],
            'status' => $validated['status'],
        ]);

        // Vérifier que l'utilisateur est récupéré correctement
        $user = User::find($validated['user_id']);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur introuvable'], 404);
        }

        // Récupérer l'utilisateur lié et envoyer la notification
        $order->user->notify(new OrderCompletedNotification($order));

        return response()->json(['message' => 'Commande finalisée avec succès', 'order' => $order], 201);
    }
}
