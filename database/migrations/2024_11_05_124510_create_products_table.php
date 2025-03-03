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
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('title'); 
                $table->text('description')->nullable();
                $table->decimal('price', 8, 2);
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->string('image')->nullable();
                $table->string('alt')->nullable();
                $table->integer('stock')->default(10); // Stock avec valeur par défaut
                $table->timestamps();
            });
        } else {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'stock')) {
                    $table->integer('stock')->default(10);
                }
                if (!Schema::hasColumn('products', 'image')) {
                    $table->string('image')->nullable();
                }
                if (!Schema::hasColumn('products', 'alt')) {
                    $table->string('alt')->nullable();
                }
                if (Schema::hasColumn('products', 'name')) {
                    $table->renameColumn('name', 'title');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
