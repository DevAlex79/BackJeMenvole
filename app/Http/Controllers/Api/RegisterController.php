<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Notifications\UserRegisteredNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur (rôle Client par défaut).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'password'      => $validated['password'], // hashé par le cast 'hashed'
            'Roles_id_role' => RoleEnum::Client->value,
        ]);

        try {
            $user->notify(new UserRegisteredNotification());
        } catch (\Throwable $e) {
            Log::warning('Notification d\'inscription non envoyée', [
                'user_id' => $user->id_user,
                'error'   => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Inscription réussie', 'user' => $user], 201);
    }
}
