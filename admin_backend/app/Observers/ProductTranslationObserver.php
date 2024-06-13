<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductTranslation;
use Str;

class ProductTranslationObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param ProductTranslation $productTranslation
     * @return void
     */
    public function creating(ProductTranslation $productTranslation): void
    {
        $this->setSlug($productTranslation);
    }

    /**
     * Handle the Product "creating" event.
     *
     * @param ProductTranslation $productTranslation
     * @return void
     */
    public function updating(ProductTranslation $productTranslation): void
    {
        $this->setSlug($productTranslation);
    }

    private function setSlug($productTranslation): void
    {
        $defaultLanguage = Language::where('default',1)->first();

        if ($defaultLanguage->locale == $productTranslation->locale){

            $slug = Str::slug($productTranslation->title);

            $productSlug = Product::firstWhere('slug',$slug);

            if ($productSlug){
                $slug = Str::slug($slug.time());
            }

            $product = Product::find($productTranslation->product_id);

            $product?->update([
                'slug' => $slug
            ]);
        }
    }
}
