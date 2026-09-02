<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des rôles applicatifs.
 *
 * Créée en premier (préfixe 0000_) car `users.Roles_id_role` y fait
 * référence. Les identifiants attendus par le code (RoleEnum) :
 *   1 = Client, 2 = Vendeur, 3 = Administrateur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_role');
            $table->string('role_name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
