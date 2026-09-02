<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des produits (articles du catalogue).
 *
 * - `users_id_user`   : vendeur/admin propriétaire (nullable, mis à NULL
 *                       si le compte est supprimé).
 * - `categories_id_category` : catégorie du produit (obligatoire).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id('id_product');
            $table->foreignId('users_id_user')
                ->nullable()
                ->constrained('users', 'id_user')
                ->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->foreignId('categories_id_category')
                ->constrained('categories', 'id')
                ->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->string('alt')->nullable();
            $table->integer('stock')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
