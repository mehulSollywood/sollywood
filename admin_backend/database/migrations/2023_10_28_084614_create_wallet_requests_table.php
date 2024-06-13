<?php

use App\Models\WalletRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('wallet_requests', function (Blueprint $table) {
            $table->id();
            $table->double('price');
            $table->foreignId('request_user_id')->nullable()->constrained('users');
            $table->foreignId('response_user_id')->nullable()->constrained('users');
            $table->string('message');
            $table->enum('status',[WalletRequest::APPROVED,WalletRequest::REJECTED,WalletRequest::PENDING]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_requests');
    }
}
