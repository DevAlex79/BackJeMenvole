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
        Schema::table('users', function (Blueprint $table) {
            
            // Supprimer l'index s'il existe
            // if (Schema::hasColumn('users', 'Roles_id_role')) {
            //     $table->dropIndex('fk_users_Roles1_idx'); // Supprime l'index
            // }

            // // Supprimer la clé étrangère si elle existe
            // $table->dropForeign(['Roles_id_role']); // Utilisez le bon nom si identifié dans l'étape 1

            // // Modifier la colonne pour ajouter une valeur par défaut
            // $table->unsignedBigInteger('Roles_id_role')->default(1)->change();

            // // Réappliquer la clé étrangère
            // $table->foreign('Roles_id_role')
            //     ->references('id_role')
            //     ->on('roles')
            //     ->onDelete('cascade');

            // Vérifier et supprimer l'index 'fk_users_Roles1_idx' s'il existe
            $indexExists = DB::select(
                "SHOW INDEX FROM users WHERE Key_name = 'fk_users_Roles1_idx'"
            );

            if (!empty($indexExists)) {
                $table->dropIndex('fk_users_Roles1_idx');
            }

            // Vérifier et supprimer la clé étrangère si elle existe
            $foreignKeyExists = DB::select(
                "SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'users' 
                AND COLUMN_NAME = 'Roles_id_role' 
                AND TABLE_SCHEMA = DATABASE()"
            );

            if (!empty($foreignKeyExists)) {
                $table->dropForeign(['Roles_id_role']);
            }

            // Modifier la colonne pour ajouter une valeur par défaut
            $table->unsignedBigInteger('Roles_id_role')->default(1)->change();

            // Réappliquer la clé étrangère
            $table->foreign('Roles_id_role')
                ->references('id_role')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // // Supprimer la clé étrangère
            // $table->dropForeign(['Roles_id_role']);

            // // Revenir à une colonne sans valeur par défaut
            // $table->unsignedBigInteger('Roles_id_role')->change();

            // // Réappliquer la clé étrangère
            // $table->foreign('Roles_id_role')
            //     ->references('id_role')
            //     ->on('roles')
            //     ->onDelete('cascade');

            // Supprimer la clé étrangère
            $foreignKeyExists = DB::select(
                "SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'users' 
                AND COLUMN_NAME = 'Roles_id_role' 
                AND TABLE_SCHEMA = DATABASE()"
            );

            if (!empty($foreignKeyExists)) {
                $table->dropForeign(['Roles_id_role']);
            }

            // Revenir à une colonne sans valeur par défaut
            $table->unsignedBigInteger('Roles_id_role')->change();
        });
    }
};
