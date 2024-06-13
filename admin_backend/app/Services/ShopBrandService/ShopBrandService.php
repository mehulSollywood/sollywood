<?php

namespace App\Services\ShopBrandService;

use App\Helpers\ResponseError;
use App\Models\ShopBrand;
use App\Services\CoreService;

class ShopBrandService extends CoreService
{

    protected function getModelClass(): string
    {
        return ShopBrand::class;
    }

    public function create($collection): array
    {
        $this->setParams($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
    }

    public function update($collection, $shop): array
    {
        $shopBrand = $shop->brands();

        $shopBrand->detach();

        $this->setParams($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];

    }

    public function destroy(array $ids): array
    {
        $items = $this->model()->whereDoesntHave('brand.products')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_511];
    }


    public function setParams($collection)
    {
        foreach (data_get($collection, 'brands', []) as $brand_id) {
            $this->model()->create([
                'shop_id'   => data_get($collection, 'shop_id'),
                'brand_id'  => $brand_id
            ]);
        }
    }



}
