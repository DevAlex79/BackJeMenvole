<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearUsersAndProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver la vérification des clés étrangères pour éviter les erreurs
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider les tables
        DB::table('users')->truncate();
        DB::table('products')->truncate();

        // Réactiver la vérification des clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "Tables `users` et `products` vidées avec succès !\n";
    }
}
