<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToBonusShops extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonus_shops', function (Blueprint $table) {
            $table->renameColumn('price','order_amount');
            $table->dateTime('expired_at');
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonus_shops', function (Blueprint $table) {
            $table->renameColumn('price','order_amount');
            $table->dropColumn('expired_at');
            $table->dropColumn('status');
        });
    }
}
