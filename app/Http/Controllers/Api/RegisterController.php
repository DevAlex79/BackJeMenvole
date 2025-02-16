<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\UserRegisteredNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Enregistre un nouvel utilisateur.
     */
    public function register(Request $request)
    {
        // Valider les données d'entrée
        $validated = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

         // Vérifier si la validation échoue
        if ($validated->fails()) {
            Log::error("Validation échouée", ['errors' => $validated->errors()]);
            return response()->json($validated->errors(), 422);
        }

        Log::info("✅ Validation réussie, création de l'utilisateur");

        // Créer l'utilisateur
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'Roles_id_role' => 1, // Par défaut
        ]);

        //mail("test@example.com", "Test Laravel Mail", "Ceci est un test d'email depuis Laravel.");

        // Envoyer une notification par email
        // Log::info("Envoi de la notification UserRegisteredNotification à " . $user->email);
        // $user->notify(new UserRegisteredNotification());
        // Log::info("Notification envoyée !");

        //Notification::send($user, new UserRegisteredNotification());

        Log::info("Utilisateur créé avec ID: " . $user->id_user);
        
        Log::info("Tentative d'envoi de notification à " . $user->email);
        try {
            $user->notify(new UserRegisteredNotification());
            Log::info("Notification envoyée avec succès !");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de la notification : " . $e->getMessage());
        }

        return response()->json(['message' => 'Inscription réussie', 'user' => $user], 201);
    }
}
