<?php

namespace Database\Seeders;

use App\Models\Brand;
use Faker\Provider\en_GB\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $brands = [
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'title' => Person::firstNameMale(),
                'img' => 1,
                'active' => 1,
            ]
        ];

        foreach ($brands as $brand){
            Brand::updateOrInsert(['id' => $brand['id']],$brand);
        }

    }
}
