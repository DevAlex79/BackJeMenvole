<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //$table->unsignedBigInteger('users_id_user')->nullable()->after('id_product'); // Ajoute la colonne après 'id'
            //$table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade'); // Ajoute la clé étrangère
            // Vérifie si la colonne n'existe pas avant de l'ajouter
            if (!Schema::hasColumn('products', 'users_id_user')) {
                $table->unsignedBigInteger('users_id_user')->nullable()->after('id_product');

                // Ajoute la contrainte de clé étrangère seulement si elle n'existe pas
                $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //$table->dropForeign(['users_id_user']);
            //$table->dropColumn('users_id_user');
             // Supprime la clé étrangère uniquement si elle existe
            if (Schema::hasColumn('products', 'users_id_user')) {
                $table->dropForeign(['users_id_user']);
                $table->dropColumn('users_id_user');
            }
        });
    }
};
