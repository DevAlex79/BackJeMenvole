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
    // public function up(): void
    // {
    //     Schema::table('orders', function (Blueprint $table) {
    //         try {
    //             if (Schema::hasColumn('orders', 'users_id_user')) {
    //                 $table->dropForeign('fk_orders_users');  // Supprime la clé étrangère
    //             }
    //         } catch (\Exception $e) {
    //             // On ignore l'erreur si la clé n'existe pas
    //         }
    //     });

    //     Schema::table('discount_coupons', function (Blueprint $table) {
    //         try {
    //             if (Schema::hasColumn('discount_coupons', 'users_id_user')) {
    //                 $table->dropForeign(['users_id_user']); 
    //             }
    //             if (Schema::hasColumn('discount_coupons', 'Orders_id_order')) {
    //                 $table->dropForeign('fk_discount_coupons_Orders1');
    //             }
    //         } catch (\Exception $e) {
    //             // On ignore l'erreur si la clé n'existe pas
    //         }
    //     });
    // }

    public function up(): void
    {
        // Suppression des clés étrangères existantes
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'Orders_id_order')) {
                //$table->dropForeign(['Orders_id_order']);
                DB::statement('ALTER TABLE order_items DROP FOREIGN KEY fk_Order_items_Orders1');
            }
            if (Schema::hasColumn('order_items', 'Products_id_product')) {
                //$table->dropForeign(['Products_id_product']);
                DB::statement('ALTER TABLE order_items DROP FOREIGN KEY fk_Order_items_Products1');
            }
        });

        Schema::table('payment', function (Blueprint $table) {
            if (Schema::hasColumn('payment', 'Orders_id_order')) {
                //$table->dropForeign(['Orders_id_order']);
                DB::statement('ALTER TABLE payment DROP FOREIGN KEY fk_Payment_Orders1');
            }
        });

        Schema::table('discount_coupons', function (Blueprint $table) {
            if (Schema::hasColumn('discount_coupons', 'Orders_id_order')) {
                //$table->dropForeign(['Orders_id_order']);
                DB::statement('ALTER TABLE discount_coupons DROP FOREIGN KEY fk_discount_coupons_Orders1');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'Roles_id_role')) {
                //$table->dropForeign(['Roles_id_role']);
                DB::statement('ALTER TABLE users DROP FOREIGN KEY users_roles_id_role_foreign');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::table('orders', function (Blueprint $table) {
    //         $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
    //     });

    //     Schema::table('discount_coupons', function (Blueprint $table) {
    //         $table->foreign('users_id_user')->references('id_user')->on('users')->onDelete('cascade');
    //         $table->foreign('Orders_id_order')->references('id_order')->on('orders')->onDelete('cascade');
    //     });
    // }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreign('Orders_id_order')->references('id_order')->on('orders')->onDelete('cascade');
            $table->foreign('Products_id_product')->references('id_product')->on('products')->onDelete('cascade');
        });

        Schema::table('payment', function (Blueprint $table) {
            $table->foreign('Orders_id_order')->references('id_order')->on('orders')->onDelete('cascade');
        });

        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->foreign('Orders_id_order')->references('id_order')->on('orders')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('Roles_id_role')->references('id_role')->on('roles')->onDelete('cascade');
        });
    }
};
