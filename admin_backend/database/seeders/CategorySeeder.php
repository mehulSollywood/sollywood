<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'id' => 1,
                'uuid' => Str::uuid(),
                'parent_id' => 0,
                'type' => 1,
                'img' => 1,
                'active' => 1,
            ]
        ];

        foreach ($categories as $category){
            Category::updateOrInsert(['id' => $category['id']],$category);
        }

        // Unit Languages
        $categoryLangs = [
            [
                'id' => 1,
                'category_id' => 1,
                'locale' => 'en',
                'title' => 'Category',
            ],
        ];

        foreach ($categoryLangs as $lang) {
            CategoryTranslation::updateOrInsert(['id' => $lang['id']], $lang);
        }
    }
}
