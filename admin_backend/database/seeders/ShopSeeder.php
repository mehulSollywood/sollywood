<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shops = [
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'user_id' => 103,
                'tax' => 1,
                'delivery_range' => 1,
                'percentage' => 1,
                'phone' => +998909999999,
                'show_type' => 1,
                'open' => true,
                'visibility' => true,
                'background_img' => 'test.jpg',
                'logo_img' => 'test.jpg',
                'min_amount' => '10',
                'status' => 'approved',
                'status_note' => 'New shop',
            ]
        ];

        foreach ($shops as $shop) {
            Shop::updateOrInsert(['id' => $shop['id']], $shop);
        }

        $shopLang = [
            [
                'id' => 1,
                'shop_id' => 1,
                'locale' => 'en',
                'title' => 'New Shop',
            ]
        ];
        foreach ($shopLang as $lang) {
            ShopTranslation::updateOrInsert(['shop_id' => $lang['shop_id']], $lang);
        }
    }
}
