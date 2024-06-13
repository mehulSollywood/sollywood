<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use Str;

class CategoryTranslationObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param CategoryTranslation $categoryTranslation
     * @return void
     */
    public function creating(CategoryTranslation $categoryTranslation): void
    {
        $this->setSlug($categoryTranslation);
    }

    /**
     * Handle the Product "creating" event.
     *
     * @param CategoryTranslation $categoryTranslation
     * @return void
     */
    public function updating(CategoryTranslation $categoryTranslation): void
    {
        $this->setSlug($categoryTranslation);
    }

    private function setSlug($categoryTranslation): void
    {
        $defaultLanguage = Language::where('default',1)->first();

        if ($defaultLanguage->locale == $categoryTranslation->locale){

            $slug = Str::slug($categoryTranslation->title);

            $categorySlug = Category::firstWhere('slug',$slug);

            if ($categorySlug){
                $slug = Str::slug($slug.time());
            }

            $category = Category::find($categoryTranslation->category_id);

            $category?->update([
                'slug' => $slug
            ]);
        }
    }
}
