<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Settings;
use App\Models\ShopPayment;

class PaymentObserver
{
    public function updated(Payment $payment)
    {
        $setting = Settings::where('key','payment_type')->where('value','admin')->first();

        if ($setting){
            $shopPayments = ShopPayment::where('payment_id', $payment->id)->get();
            $shopPayments->map(function ($q) use ($payment){
               $q->update([
                   'client_id' => $payment->client_id,
                   'secret_id' => $payment->secret_id,
                   'merchant_email' => $payment->merchant_email,
                   'payment_key' => $payment->payment_key,
               ]);
            });

        }

    }
}
