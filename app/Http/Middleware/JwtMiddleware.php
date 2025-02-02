<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['error' => 'Token invalide ou manquant'], 401);
        }

        return $next($request);
    }
}
