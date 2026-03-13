<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Vérifie le token JWT et, si des rôles sont spécifiés, contrôle
     * que l'utilisateur possède l'un des rôles autorisés.
     *
     * Utilisation dans les routes :
     *   ->middleware('jwt.auth')        → tout utilisateur authentifié
     *   ->middleware('jwt.auth:2,3')    → Vendeur (2) ou Administrateur (3)
     *   ->middleware('jwt.auth:3')      → Administrateur uniquement
     *
     * Les valeurs numériques correspondent aux cases de RoleEnum.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     * @param  int|string                ...$roles  Identifiants de rôles autorisés
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            // Authentifie l'utilisateur via le token JWT du header Authorization
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
            }

            // Si des rôles sont requis pour cette route, vérification de l'appartenance
            if (!empty($roles)) {
                $userRole = (int) $user->Roles_id_role;

                // Les paramètres de route arrivent en chaîne → conversion en entiers
                $allowedRoles = array_map('intval', $roles);

                if (!in_array($userRole, $allowedRoles, strict: true)) {
                    return response()->json(['error' => 'Accès interdit. Rôle insuffisant.'], 403);
                }
            }

        } catch (\Exception $e) {
            return response()->json(['error' => 'Token invalide ou expiré : ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}
