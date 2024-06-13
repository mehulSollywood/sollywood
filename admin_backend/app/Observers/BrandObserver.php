<?php

namespace App\Observers;

use App\Models\Brand;
use Illuminate\Support\Str;

class BrandObserver
{
    /**
     * Handle the Category "creating" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function creating(Brand $brand)
    {
        $brand->uuid = Str::uuid();
        $this->setSlug($brand);

    }

    /**
     * Handle the Brand "created" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function created(Brand $brand)
    {
        //
    }

    /**
     * Handle the Brand "updated" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function updated(Brand $brand)
    {
        $this->setSlug($brand);
    }

    /**
     * Handle the Brand "deleted" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function deleted(Brand $brand)
    {
        //
    }

    /**
     * Handle the Brand "restored" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function restored(Brand $brand)
    {
        //
    }

    /**
     * Handle the Brand "force deleted" event.
     *
     * @param Brand $brand
     * @return void
     */
    public function forceDeleted(Brand $brand)
    {
        //
    }

    private function setSlug($brand): void
    {
        $slug = Str::slug($brand->title);

        $brandSlug = Brand::firstWhere('slug', $slug);

        if ($brandSlug) {
            $slug = Str::slug($slug . time());
        }

        $brand?->update([
            'slug' => $slug
        ]);
    }
}
