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
        ]);

        // Client User
        User::create([
            'username' => 'Client',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
            'Roles_id_role' => 1, // ID correspondant au rôle "Client"
        ]);

        // Vendeur User
        User::create([
            'username' => 'Vendeur',
            'email' => 'vendeur@example.com',
            'password' => Hash::make('password'),
            'Roles_id_role' => 2, // ID correspondant au rôle "Vendeur"
        ]);
    }
}
