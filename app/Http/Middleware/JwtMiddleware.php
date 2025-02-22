<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    // public function handle(Request $request, Closure $next)
    // {
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
            
    //         if (!$user) {
    //             return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    //         }

    //         // Vérifier si un rôle est requis
    //         if (!empty($roles)) {
    //             $userRole = $user->Roles_id_role; // Vérifie le rôle de l'utilisateur connecté
                
    //             // Convertir les rôles acceptés en nombres si besoin
    //             $roles = array_map(fn($role) => (int) $role, $roles);

    //             if (!in_array($userRole, $roles)) {
    //                 return response()->json(['error' => 'Accès interdit. Rôle insuffisant.'], 403);
    //             }
    //         }
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    //         return response()->json(['error' => 'Token expiré'], 401);
    //     } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
    //         return response()->json(['error' => 'Token invalide'], 401);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Problème avec le token: ' . $e->getMessage()], 401);
    //     }

    //     return $next($request);
    //}
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

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['error' => 'Token expiré'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalide'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Problème avec le token: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }
}
