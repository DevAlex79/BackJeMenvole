<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        // Toutes les méthodes sauf login nécessitent un token JWT valide.
        // L'inscription est gérée exclusivement par RegisterController.
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Authentifie un utilisateur et retourne un token JWT.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            // TTL en secondes (config jwt.ttl est en minutes)
            'expires_in'   => Auth::factory()->getTTL() * 60,
            'user'         => [
                'id'       => $user->id_user,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->Roles_id_role,
            ],
        ]);
    }

    /**
     * Déconnecte l'utilisateur en invalidant son token JWT (blacklist).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Génère un nouveau token JWT à partir d'un token valide non expiré.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Retourne les informations de l'utilisateur connecté.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        return response()->json($user);
    }

    /**
     * Formate la réponse contenant le token JWT.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken(string $token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::factory()->getTTL() * 60,
        ]);
    }
}
