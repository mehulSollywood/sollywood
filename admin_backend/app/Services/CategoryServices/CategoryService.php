<?php

namespace App\Services\CategoryServices;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Models\Product;
use App\Services\CoreService;
use App\Services\Interfaces\CategoryServiceInterface;

class CategoryService extends CoreService implements CategoryServiceInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    /**
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
      
        if (data_get($collection, 'parent_id')) {

            $productExist = Product::where('category_id', data_get($collection, 'parent_id'))->first();

            if ($productExist) {
                return [
                    'status' => false,
                    'code' => 'ERROR_' . ResponseError::ERROR_501,
                    'message' => __('category.has_products')
                ];
            }
        }

        $category = $this->model()->create($this->setCategoryParams($collection));
        $this->setTranslations($category, $collection);

        if ($category && isset($collection->images)) {
            $category->update(['img' => $collection->images[0]]);
            $category->uploads($collection->images);
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    /**
     * @param string $uuid
     * @param $collection
     * @return array
     */
    public function update(string $uuid, $collection): array
    {
        $category = $this->model()->firstWhere('uuid', $uuid);

        $category->update($this->setCategoryParams($collection));
        $this->setTranslations($category, $collection);

        if (isset($collection->images)) {
            $category->galleries()->delete();
            $category->uploads($collection->images);
            $category->update(['img' => $collection->images[0]]);
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    /**
     * @param array $ids
     * @return array
     */
    public function destroy(array $ids): array
    {
        $items = $this->model()->whereDoesntHave('children')->whereDoesntHave('products')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_509];
    }

    /**
     * Set Category params for Create & Update function
     * @param $collection
     * @return array
     */
    private function setCategoryParams($collection): array
    {
        if ($collection->parent_id == 0)
            $collection->parent_id = null;

        return [
            'keywords' => $collection->keywords ?? null,
             'referralPercentage' => $collection->referralPercentage ?? null,
             'gstPercentage' => $collection->gstPercentage ?? null,
            'parent_id' => $collection->parent_id ?? null,
            'type' => $collection->type,
            'active' => $collection->active ?? 0,
        ];
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();

        foreach ($collection->title as $index => $value) {
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'title' => $value,
                    'description' => $collection->description[$index] ?? null,
                    'locale' => $index,
                ]);
            }
        }
    }
}
