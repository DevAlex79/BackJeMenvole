<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         // Désactiver AUTO_INCREMENT temporairement
        DB::statement('ALTER TABLE orders MODIFY id_order INT NOT NULL');

         // Supprimer la clé primaire
        Schema::table('orders', function (Blueprint $table) {
            $table->dropPrimary(['id_order']);
        });

         // Modifier la colonne en BIGINT UNSIGNED
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('id_order')->change();
        });

         // Réattribuer la clé primaire et AUTO_INCREMENT
        DB::statement('ALTER TABLE orders MODIFY id_order BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id_order)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Désactiver AUTO_INCREMENT temporairement
        DB::statement('ALTER TABLE orders MODIFY id_order BIGINT UNSIGNED NOT NULL');

        // Supprimer la clé primaire
        Schema::table('orders', function (Blueprint $table) {
            $table->dropPrimary(['id_order']);
        });

        // Revenir à INT(11)
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('id_order')->change();
        });

        // Réattribuer la clé primaire et AUTO_INCREMENT
        DB::statement('ALTER TABLE orders MODIFY id_order INT NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id_order)');
    }
};
