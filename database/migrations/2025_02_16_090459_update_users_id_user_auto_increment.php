<?php

use Illuminate\Database\Migrations\Migration;
//use Illuminate\Database\Schema\Blueprint;
//use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Désactive temporairement les contraintes FK

        // Étape 1 : Supprimer la clé primaire actuelle si elle existe
        $primaryKeyExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', 'users')
            ->where('CONSTRAINT_TYPE', 'PRIMARY KEY')
            ->exists();

        if ($primaryKeyExists) {
            DB::statement('ALTER TABLE users DROP PRIMARY KEY');
        }

        // Étape 2 : Modifier `id_user` en `BIGINT UNSIGNED`
        DB::statement('ALTER TABLE users MODIFY id_user BIGINT UNSIGNED NOT NULL');

        // Étape 3 : Réappliquer la clé primaire avec AUTO_INCREMENT
        DB::statement('ALTER TABLE users MODIFY id_user BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id_user)');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Réactive les contraintes FK
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Suppression d'AUTO_INCREMENT
        DB::statement('ALTER TABLE users MODIFY id_user BIGINT UNSIGNED NOT NULL');

        // Réappliquer la clé primaire (sans AUTO_INCREMENT)
        DB::statement('ALTER TABLE users ADD PRIMARY KEY (id_user)');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
