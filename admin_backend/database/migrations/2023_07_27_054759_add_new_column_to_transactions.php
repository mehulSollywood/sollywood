<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('request')->nullable();
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('progress','paid','canceled','rejected','debit') NOT NULL DEFAULT 'progress'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('request');
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('progress','paid','canceled','rejected') NOT NULL DEFAULT 'progress'");

        });
    }
}
