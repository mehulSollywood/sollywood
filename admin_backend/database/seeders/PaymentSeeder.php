<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\PaymentTranslation;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $payments = [
            [
                'id' => 1,
                'tag' => 'cash',
                'input' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tag' => 'wallet',
                'input' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'tag' => 'paypal',
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'tag' => 'stripe',
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'tag' => 'paystack',
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'tag' => 'razorpay',
                'input' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($payments as $item) {
            Payment::updateOrInsert(['id' => $item['id']], $item);
        }

        $paymentLang = [
            [
                'id' => 1,
                'payment_id' => 1,
                'locale' => 'en',
                'title' => 'Cash',
            ],
            [
                'id' => 2,
                'payment_id' => 2,
                'locale' => 'en',
                'title' => 'Wallet',
            ],
            [
                'id' => 3,
                'payment_id' => 3,
                'locale' => 'en',
                'title' => 'Paypal',
            ],
            [
                'id' => 4,
                'payment_id' => 4,
                'locale' => 'en',
                'title' => 'Stripe',
            ],
            [
                'id' => 5,
                'payment_id' => 5,
                'locale' => 'en',
                'title' => 'Paystack',
            ],
            [
                'id' => 6,
                'payment_id' => 6,
                'locale' => 'en',
                'title' => 'Razorpay',
            ],
        ];

        foreach ($paymentLang as $lang) {
            PaymentTranslation::updateOrInsert(['payment_id' => $lang['payment_id']], $lang);
        }
    }
}
