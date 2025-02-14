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
           //Suppression des clés étrangères existantes avant modification des colonnes
        // Schema::table('orders', function (Blueprint $table) {
        //     if (Schema::hasColumn('orders', 'users_id_user')) {
        //         DB::statement('ALTER TABLE orders DROP FOREIGN KEY fk_orders_users');
        //     }
        // });

        // Schema::table('discount_coupons', function (Blueprint $table) {
        //     if (Schema::hasColumn('discount_coupons', 'users_id_user')) {
        //         DB::statement('ALTER TABLE discount_coupons DROP FOREIGN KEY fk_discount_coupons_Orders1');
        //     }
        // });

         //Vérification des clés étrangères avant suppression
        if (DB::table('information_schema.TABLE_CONSTRAINTS')
        ->where('TABLE_SCHEMA', env('DB_DATABASE'))
        ->where('TABLE_NAME', 'orders')
        ->where('CONSTRAINT_NAME', 'fk_orders_users')
        ->exists()) {
        DB::statement('ALTER TABLE orders DROP FOREIGN KEY fk_orders_users');
    }

    if (DB::table('information_schema.TABLE_CONSTRAINTS')
        ->where('TABLE_SCHEMA', env('DB_DATABASE'))
        ->where('TABLE_NAME', 'discount_coupons')
        ->where('CONSTRAINT_NAME', 'fk_discount_coupons_Orders1')
        ->exists()) {
        DB::statement('ALTER TABLE discount_coupons DROP FOREIGN KEY fk_discount_coupons_Orders1');
    }


        //Modification des colonnes pour qu'elles soient compatibles avec les clés étrangères
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id_product')->change();
        });

        //Réattachement des clés étrangères
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         //Suppression des clés étrangères avant retour en arrière
        // Schema::table('orders', function (Blueprint $table) {
        //     $table->dropForeign(['users_id_user']);
        // });

        // Schema::table('discount_coupons', function (Blueprint $table) {
        //     $table->dropForeign(['users_id_user']);
        // });

        //Vérification avant suppression des clés étrangères
        if (DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', 'orders')
            ->where('CONSTRAINT_NAME', 'orders_users_id_user_foreign')
            ->exists()) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['users_id_user']);
            });
        }

        if (DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', 'discount_coupons')
            ->where('CONSTRAINT_NAME', 'discount_coupons_users_id_user_foreign')
            ->exists()) {
            Schema::table('discount_coupons', function (Blueprint $table) {
                $table->dropForeign(['users_id_user']);
            });
        }

        //Revenir aux types INT(11)
        Schema::table('users', function (Blueprint $table) {
            $table->integer('id_user')->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('id_product')->change();
        });

        //Réattachement des clés étrangères
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
        });
    }
};
