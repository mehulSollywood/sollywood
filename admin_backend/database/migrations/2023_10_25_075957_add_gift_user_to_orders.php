<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGiftUserToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('gift_user_id')->nullable()->constrained('users');
            $table->foreignId('gift_cart_id')->nullable()->constrained('shop_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_gift_user_id_foreign');
            $table->dropColumn('gift_user_id');
            $table->dropForeign('orders_gift_cart_id_foreign');
            $table->dropColumn('gift_cart_id');
        });
    }
}
