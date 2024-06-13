<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionParcelOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parcel_orders', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->constrained('parcel_order_settings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('instruction')->nullable();
            $table->text('description')->nullable();
            $table->string('qr_value')->nullable();
            $table->boolean('notify')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parcel_orders', function (Blueprint $table) {
            $table->dropColumn('instruction');
            $table->dropColumn('description');
            $table->dropColumn('qr_value');
            $table->dropColumn('notify');
            $table->dropForeign('parcel_orders_type_id_foreign');
            $table->dropColumn('type_id');
        });
    }
}
