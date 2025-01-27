<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\UserRegisteredNotification;

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

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        // Créer l'utilisateur
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'Roles_id_role' => 1, // Par défaut
        ]);

        // Envoyer une notification par email
        $user->notify(new UserRegisteredNotification());

        return response()->json(['message' => 'Inscription réussie', 'user' => $user], 201);
    }
}
