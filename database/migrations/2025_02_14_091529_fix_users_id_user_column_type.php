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
        Schema::table('orders', function (Blueprint $table) {
             // Vérifie si la colonne existe avant modification
            if (Schema::hasColumn('orders', 'users_id_user')) {
                // Modifier la colonne en BIGINT UNSIGNED
                $table->unsignedBigInteger('users_id_user')->change();

                // Ajouter la clé étrangère (car elle n'existe pas)
                $table->foreign('users_id_user')
                    ->references('id_user')->on('users')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'users_id_user')) {
                // Supprimer la clé étrangère si elle existe
                try {
                    $table->dropForeign(['users_id_user']);
                } catch (\Exception $e) {
                    // Ignorer l'erreur si la clé n'existe pas
                }

                // Remettre users_id_user en INT(11)
                $table->integer('users_id_user')->change();
            }
        });
    }
};
