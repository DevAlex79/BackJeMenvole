<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coupons de réduction (fonctionnalité présente en base mais pas encore
 * exposée par l'API — table conservée pour ne pas perdre le schéma).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_coupons', function (Blueprint $table) {
            $table->id('id_coupon');
            $table->string('code')->unique();
            $table->decimal('discount_value', 8, 2);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->integer('usage_limit')->default(1);
            $table->integer('times_used')->default(0);
            $table->dateTime('expires_at')->nullable();
            $table->foreignId('users_id_user')
                ->constrained('users', 'id_user')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_coupons');
    }
};
