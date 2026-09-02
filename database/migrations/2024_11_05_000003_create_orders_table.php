<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table des commandes.
 *
 * `cart` stocke le panier au format JSON (liste d'items id_product +
 * quantity + prix figé à l'achat). `total_price` est recalculé côté
 * serveur au moment de la commande, jamais fourni tel quel par le client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->foreignId('users_id_user')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->decimal('total_price', 10, 2);
            $table->json('cart');
            $table->string('status', 50)->default('en attente');
            $table->string('shipment_type')->nullable();
            $table->decimal('shipment_price', 8, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
