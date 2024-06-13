<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdRecipeProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recipe_products', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('shop_products')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recipe_products', function (Blueprint $table) {
            $table->dropForeign('shop_products_product_id_foreign');
            $table->dropColumn('product_id');
        });
    }
}
