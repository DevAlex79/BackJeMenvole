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
            if (!Schema::hasColumn('orders', 'users_id_user')) {
                $table->unsignedBigInteger('users_id_user')->nullable()->after('id_order');
                $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
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
                $table->dropForeign(['users_id_user']);
                $table->dropColumn('users_id_user');
            }
        });
    }
};
