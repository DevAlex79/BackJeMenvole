<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'username' => 'Admin', // Aligner avec "username" en BDD
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'Roles_id_role' => 3, // ID correspondant au rôle "Administrateur"
            'email_verified_at' => now(), // Définit comme vérifié
            'remember_token' => \Illuminate\Support\Str::random(10), // Génère un token aléatoire
        ]);

        // Client User
        User::create([
            'username' => 'Client',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'Roles_id_role' => 1, // ID correspondant au rôle "Client"
            'email_verified_at' => null, // Non vérifié
            'remember_token' => null,
        ]);

        // Vendeur User
        User::create([
            'username' => 'Vendeur',
            'email' => 'vendeur@example.com',
            'password' => Hash::make('password'),
            'Roles_id_role' => 2, // ID correspondant au rôle "Vendeur"
            'email_verified_at' => now(),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
    }
}
