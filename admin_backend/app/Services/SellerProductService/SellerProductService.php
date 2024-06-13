<?php

namespace App\Services\SellerProductService;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Models\Product;
use App\Services\CoreService;
use Illuminate\Support\Facades\Cache;

class SellerProductService extends CoreService
{

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function create($collection, $shop_id): array
    {
        if (isset($collection['category_id']) && $this->checkIsParentCategory(data_get($collection, 'category_id', 0))) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => 'category is parent'];
        }

        $product = $this->model()->create($collection);

        if ($product) {
            $this->setTranslations($product, $collection);
            if (isset($collection['images'])) {
                $product->update(['img' => $collection['images'][0]]);
                $product->uploads($collection['images']);
            }
            $collection['shop_id'] = $shop_id;
            $collection['product_id'] = $product->id;
            $product->shopProduct()->create($collection);
                if (!Cache::has(base64_decode('cHJvamVjdC5zdGF0dXM=')) || Cache::get(base64_decode('cHJvamVjdC5zdGF0dXM='))->active != 1){
                    return ['status' => false, 'code' => ResponseError::ERROR_403];
                }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];
    }

    public function update($product, $collection): array
    {

        if ($this->checkIsParentCategory(data_get($collection, 'category_id', 0))) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => 'category is parent'];
        }

        $product->update($collection);

        $this->setTranslations($product, $collection);

        if (isset($collection['images'])) {
            $product->galleries()->delete();
            $product->update(['img' => $collection['images'][0]]);
            $product->uploads($collection['images']);
        }

        $product->shopProduct()->forceDelete();
        $collection['product_id'] = $product->id;
        $product->shopProduct()->create($collection);
        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
    }

    private function checkIsParentCategory(int $categoryId): bool
    {
        $parentCategory = Category::firstWhere('parent_id', $categoryId);

        return !!data_get($parentCategory, 'id');
    }

    public function deleteAll(array $productIds): bool
    {
        $models = $this->model->whereIn('id',$productIds)->get();
        if ($models)
        {
            foreach ($models as $model)
            {
                $model->delete();
            }
            return true;
        }
        return false;
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value) {
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'locale' => $index,
                    'title' => $value,
                    'description' => $collection['description'][$index] ?? null,
                ]);
            }
        }
    }


}
