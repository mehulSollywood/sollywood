<?php

namespace Database\Seeders;

use App\Models\ShopProduct;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shopProducts = [
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'shop_id' => 1,
                'min_qty' => 1,
                'max_qty' => 100,
                'active' => 1,
                'quantity' => 1000,
                'product_id' => 1,
                'price' => 100,
                'tax' => 1
            ]
        ];

        foreach ($shopProducts as $shopProduct){
            ShopProduct::updateOrInsert(['id' => $shopProduct['id']],$shopProduct);
        }
    }
}
