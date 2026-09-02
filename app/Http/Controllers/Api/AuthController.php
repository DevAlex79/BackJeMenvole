<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Authentifie un utilisateur et retourne un token JWT.
     *
     * NB : les routes /logout, /refresh, /user-profile sont protégées par
     * le middleware 'jwt.auth' dans routes/api.php. (L'ancien
     * `$this->middleware()` en constructeur n'a plus aucun effet en
     * Laravel 11 et a été retiré.)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Identifiants incorrects'], 401);
        }

        return $this->respondWithToken($token, Auth::user());
    }

    /**
     * Invalide le token JWT courant (blacklist).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Émet un nouveau token à partir d'un token valide.
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Retourne l'utilisateur connecté.
     */
    public function userProfile()
    {
        return response()->json(Auth::user());
    }

    /**
     * Formate la réponse contenant le token JWT.
     */
    protected function respondWithToken(string $token, $user = null)
    {
        $payload = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::factory()->getTTL() * 60, // secondes
        ];

        if ($user) {
            $payload['user'] = [
                'id'       => $user->id_user,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->Roles_id_role,
            ];
        }

        return response()->json($payload);
    }
}
