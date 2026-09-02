<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôle que l'utilisateur authentifié possède l'un des rôles autorisés.
 *
 * À utiliser APRÈS le middleware d'authentification `jwt.auth` (fourni par
 * tymon/jwt-auth), qui garantit qu'un utilisateur est bien connecté :
 *
 *   ->middleware(['jwt.auth', 'role:3'])      → Administrateur uniquement
 *   ->middleware(['jwt.auth', 'role:2,3'])    → Vendeur (2) ou Administrateur (3)
 *
 * Les valeurs numériques correspondent aux cases de App\Enums\RoleEnum.
 *
 * NB : l'ancien middleware maison `jwt.auth` (App\Http\Middleware\JwtMiddleware)
 * n'était enregistré nulle part — l'alias `jwt.auth` pointe en réalité vers
 * Tymon\JWTAuth, qui ignore les paramètres `:role`. Toutes les restrictions
 * de rôle des routes étaient donc inopérantes.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $allowedRoles = array_map('intval', $roles);

        if ($allowedRoles !== [] && ! in_array((int) $user->Roles_id_role, $allowedRoles, true)) {
            return response()->json(['error' => 'Accès interdit. Rôle insuffisant.'], 403);
        }

        return $next($request);
    }
}
