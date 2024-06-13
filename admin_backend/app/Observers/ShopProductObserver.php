<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\ShopBrand;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Illuminate\Support\Str;

class ShopProductObserver
{
    private ShopBrand $shopBrand;
    private ShopCategory $shopCategory;
    private Category $category;

    public function __construct(ShopBrand $shopBrand,ShopCategory $shopCategory,Category $category)
    {
        $this->shopBrand = $shopBrand;
        $this->shopCategory = $shopCategory;
        $this->category = $category;
    }

    public function creating(ShopProduct $shopProduct)
    {
        $shopProduct->uuid = Str::uuid();
    }
    /**
     * Handle the ShopProduct "created" event.
     *
     * @param ShopProduct $shopProduct
     * @return void
     */
    public function created(ShopProduct $shopProduct): void
    {
        $shopId = $shopProduct->shop_id;

        $this->setShopBrand($shopProduct,$shopId);

        $this->setShopCategory($shopProduct,$shopId);
    }

    /**
     * Handle the ShopProduct "updated" event.
     *
     * @param ShopProduct $shopProduct
     * @return void
     */
    public function updated(ShopProduct $shopProduct): void
    {
        $shopId = $shopProduct->shop_id;

        $this->setShopBrand($shopProduct,$shopId);

        $this->setShopCategory($shopProduct,$shopId);

    }

    /**
     * Handle the ShopProduct "deleted" event.
     *
     * @param ShopProduct $shopProduct
     * @return void
     */
    public function deleted(ShopProduct $shopProduct)
    {
        //
    }

    /**
     * Handle the ShopProduct "restored" event.
     *
     * @param ShopProduct $shopProduct
     * @return void
     */
    public function restored(ShopProduct $shopProduct)
    {
        //
    }

    /**
     * Handle the ShopProduct "force deleted" event.
     *
     * @param ShopProduct $shopProduct
     * @return void
     */
    public function forceDeleted(ShopProduct $shopProduct)
    {
        //
    }

    public function setShopBrand($shopProduct,int $shopId): void
    {
        /** @var ShopProduct $shopProduct */
        $brandId = $shopProduct->product?->brand_id;
        if ($brandId){
            $shopBrand = $this->shopBrand
                ->where('shop_id', $shopId)
                ->where('brand_id', $brandId)
                ->first();

            if (!$shopBrand) {
                $this->shopBrand->create(['shop_id' => $shopId, 'brand_id' => $brandId]);
            }
        }

    }

    public function setShopCategory($shopProduct,int $shopId): void
    {
        /** @var ShopProduct $shopProduct */

        $category_id = $shopProduct->product?->category_id;
        $category = $this->category->where('id',$category_id)->first();
        if ($category)
        {
            $shopCategory = $this->shopCategory
                ->where('shop_id', $shopId)
                ->where('category_id', $category->id)
                ->first();
            if (!$shopCategory) {
                $this->shopCategory->create(['shop_id' => $shopId, 'category_id' => $category->id]);
            }
        }
    }
}
