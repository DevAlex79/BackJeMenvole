<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Vérification des rôles si spécifiés
            if (!empty($roles)) {
                $userRole = (int) $user->Roles_id_role;

                // Convertir les rôles en tableau d'entiers
                $allowedRoles = array_map('intval', $roles);

                if (!in_array($userRole, $allowedRoles)) {
                    return response()->json(['error' => 'Accès interdit. Rôle insuffisant.'], 403);
                }
            }
        
        } catch (\Exception $e) {
            return response()->json(['error' => 'Problème avec le token: ' . $e->getMessage()], 401);
        }

        return $next($request);

    }
}
