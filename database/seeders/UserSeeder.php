<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Comptes de démonstration.
     *
     * Le mot de passe vient de SEED_PASSWORD (.env) pour ne pas figer un
     * identifiant connu dans le dépôt. À NE PAS exécuter en production.
     */
    public function run(): void
    {
        $password = env('SEED_PASSWORD', 'password');

        $accounts = [
            ['username' => 'Admin',    'email' => 'admin@example.com',    'role' => RoleEnum::Administrateur],
            ['username' => 'Vendeur1', 'email' => 'vendeur1@example.com', 'role' => RoleEnum::Vendeur],
            ['username' => 'User1',    'email' => 'user1@example.com',     'role' => RoleEnum::Client],
            ['username' => 'User2',    'email' => 'user2@example.com',     'role' => RoleEnum::Client],
        ];

        foreach ($accounts as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'username'          => $account['username'],
                    'password'          => $password, // hashé par le cast 'hashed'
                    'Roles_id_role'     => $account['role']->value,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
