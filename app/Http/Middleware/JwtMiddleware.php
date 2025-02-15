<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    // public function handle(Request $request, Closure $next): Response
    // {
    //     if (!Auth::guard('api')->check()) {
    //         return response()->json(['error' => 'Token invalide ou manquant'], 401);
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Utilisateur non authentifié'], 401);
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
