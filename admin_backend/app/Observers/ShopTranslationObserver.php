<?php

namespace App\Observers;

use App\Models\Language;
use App\Models\Shop;
use App\Models\ShopTranslation;
use Str;

class ShopTranslationObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param ShopTranslation $shopTranslation
     * @return void
     */
    public function creating(ShopTranslation $shopTranslation): void
    {
        $this->setSlug($shopTranslation);
    }

    /**
     * Handle the Product "creating" event.
     *
     * @param ShopTranslation $shopTranslation
     * @return void
     */
    public function updating(ShopTranslation $shopTranslation): void
    {
        $this->setSlug($shopTranslation);
    }

    private function setSlug($shopTranslation): void
    {
        $defaultLanguage = Language::where('default',1)->first();

        if ($defaultLanguage->locale == $shopTranslation->locale){

            $slug = Str::slug($shopTranslation->title);

            $shopSlug = Shop::firstWhere('slug',$slug);

            if ($shopSlug){
                $slug = Str::slug($slug.time());
            }

            $shop = Shop::find($shopTranslation->shop_id);

            $shop?->update([
                'slug' => $slug
            ]);
        }
    }
}
