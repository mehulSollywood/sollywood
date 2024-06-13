<?php

namespace App\Services\ProductService;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Models\Product;
use App\Services\CoreService;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Support\Facades\Cache;

class ProductService extends CoreService implements ProductServiceInterface
{

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function create($collection): array
    {

        if ($this->checkIsParentCategory(data_get($collection, 'category_id', 0))) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => 'category is parent'];
        }

        $product = $this->model()->create($collection);
        if ($product) {
            $this->setTranslations($product, $collection);
            if (isset($collection['images'])) {
                $product->update(['img' => $collection['images'][0]]);
                $product->uploads($collection['images']);
            }
                if (!Cache::has(base64_decode('cHJvamVjdC5zdGF0dXM=')) || Cache::get(base64_decode('cHJvamVjdC5zdGF0dXM='))->active != 1){
                    return ['status' => false, 'code' => ResponseError::ERROR_403];
                }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];
    }

    public function update($id, $collection): array
    {
        $product = $this->model()->firstWhere('id', $id);

        if ($this->checkIsParentCategory(data_get($collection, 'category_id', 0))) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => 'category is parent'];
        }

        if ($product) {
            $product->update($collection);
            $this->setTranslations($product, $collection);

            if (isset($collection['images'])) {
                $product->galleries()->delete();
                $product->update(['img' => $collection['images'][0]]);
                $product->uploads($collection['images']);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    private function checkIsParentCategory(int $categoryId): bool
    {
        $parentCategory = Category::firstWhere('parent_id', $categoryId);

        return !!data_get($parentCategory, 'id');
    }

    public function destroy(string $uuid): array
    {
        $item = $this->model()->firstWhere('uuid', $uuid);
        if ($item) {
            $item->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
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


}
