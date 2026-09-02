<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    /**
     * En-têtes d'authentification portant un vrai token JWT.
     *
     * Le middleware `jwt.auth` (tymon/jwt-auth) exige un token présent
     * dans la requête : `actingAs()` seul ne suffit pas pour les routes
     * protégées.
     *
     * @return array<string, string>
     */
    protected function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . JWTAuth::fromUser($user)];
    }
}
