<?php

namespace App\Observers;

use App\Models\Wallet;

class WalletObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param Wallet $wallet
     * @return void
     */
    public function creating(Wallet $wallet): void
    {
        $wallet->code = uniqid();
    }
}
