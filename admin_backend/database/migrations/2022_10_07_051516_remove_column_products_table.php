<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_shop_id_foreign');
            $table->dropColumn('shop_id');
            $table->dropColumn('min_qty');
            $table->dropColumn('max_qty');
            $table->dropColumn('active');
            $table->dropColumn('quantity');
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->constrained('shops')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('min_qty')->nullable();
            $table->integer('max_qty')->nullable();
            $table->boolean('active')->default(0);
            $table->integer('quantity');
            $table->integer('price');
        });
    }
}
