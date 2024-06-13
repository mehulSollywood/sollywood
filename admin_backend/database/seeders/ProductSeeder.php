<?php

namespace Database\Seeders;

use App\Models\Product;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = [
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'category_id' => 1,
                'brand_id' => 1,
                'unit_id' => 1,
                'keywords' => 'keyword',
                'img' => 'test.jpg',
                'qr_code' => '312aq45',
            ]
        ];

        foreach ($products as $product){
            Product::updateOrInsert(['id' => $product['id']],$product);
        }
    }
}
