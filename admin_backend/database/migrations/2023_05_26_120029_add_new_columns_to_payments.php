<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('client_id')->nullable();
            $table->text('secret_id')->nullable();
            $table->string('merchant_email')->nullable();
            $table->string('payment_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('client_id');
            $table->dropColumn('secret_id');
            $table->dropColumn('merchant_email');
            $table->dropColumn('payment_key');
        });
    }
}
