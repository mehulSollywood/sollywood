<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBonusProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonus_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_product_id')->nullable()->constrained('shop_products')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('bonus_product_id')->nullable()->constrained('shop_products')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('bonus_quantity');
            $table->integer('shop_product_quantity');
            $table->dateTime('expired_at');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonus_products');
    }
}
