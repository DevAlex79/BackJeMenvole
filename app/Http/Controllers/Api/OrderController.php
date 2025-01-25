<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Notifications\OrderCompletedNotification;

class OrderController extends Controller
{
    /**
     * Finalise une commande.
     */
    public function completeOrder(Request $request)
    {
        // Valider les données d'entrée
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'items' => 'required|array',
        ]);

        // Créer la commande
        $order = Order::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'items' => json_encode($request->items),
        ]);

        // Notifier l'utilisateur
        $order->user->notify(new OrderCompletedNotification($order));

        return response()->json(['message' => 'Commande finalisée avec succès', 'order' => $order], 201);
    }
}
