<?php

namespace App\Services\ShopCategoryService;

use App\Helpers\ResponseError;
use App\Models\ShopCategory;
use App\Services\CoreService;

class ShopCategoryService extends CoreService
{

    protected function getModelClass(): string
    {
        return ShopCategory::class;
    }

    public function create($collection): array
    {
        $this->setParams($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
    }

    public function update($collection, $shop): array
    {
        $shopCategory = $shop->categories();
        $shopCategory->detach();

        $this->setParams($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];

    }

    /**
     * @param array $ids
     * @return array
     */
    public function destroy(array $ids): array
    {
        $items = $this->model()->whereDoesntHave('category.children')->whereDoesntHave('category.products')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_509];
    }

    public function setParams($collection)
    {
        foreach (data_get($collection, 'categories', []) as $category_id) {
            $this->model()->create([
                'shop_id'       => data_get($collection, 'shop_id'),
                'category_id'   => $category_id
            ]);
        }
    }


}
