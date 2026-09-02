<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Inscription, connexion et protection des routes par token JWT.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_inscription_cree_un_client(): void
    {
        // L'inscription force le rôle Client : la ligne roles doit exister
        // (en production, garantie par RoleSeeder).
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $response = $this->postJson('/api/register', [
            'username'              => 'Alice',
            'email'                 => 'alice@example.com',
            'password'              => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'alice@example.com', 'Roles_id_role' => 1]);
    }

    public function test_login_retourne_un_token(): void
    {
        User::factory()->create(['email' => 'bob@example.com']);

        $response = $this->postJson('/api/login', [
            'email'    => 'bob@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user']);
    }

    public function test_login_refuse_de_mauvais_identifiants(): void
    {
        User::factory()->create(['email' => 'bob@example.com']);

        $this->postJson('/api/login', ['email' => 'bob@example.com', 'password' => 'faux'])
            ->assertUnauthorized();
    }

    public function test_route_protegee_exige_un_token(): void
    {
        $this->getJson('/api/user-profile')->assertUnauthorized();

        $user = User::factory()->create();
        $this->getJson('/api/user-profile', $this->authHeaders($user))->assertOk();
    }
}
