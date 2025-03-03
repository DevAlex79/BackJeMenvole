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
        Schema::table('users', function (Blueprint $table) {
            // Supprime la colonne erronée "role" si elle existe
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            // Ajoute la colonne `Roles_id_role` comme clé étrangère
            $table->unsignedBigInteger('Roles_id_role')->after('password')->default(1);
            $table->foreign('Roles_id_role')->references('id_role')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['Roles_id_role']);
            $table->dropColumn('Roles_id_role');
    
            // Restaurer "role" si nécessaire
            $table->enum('role', ['admin', 'client'])->default('client');
        });
    }
};
