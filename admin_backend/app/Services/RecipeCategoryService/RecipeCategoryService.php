<?php

namespace App\Services\RecipeCategoryService;

use App\Helpers\ResponseError;
use App\Models\RecipeCategory;
use App\Models\ShopPayment;
use App\Services\CoreService;

class RecipeCategoryService extends CoreService
{

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return RecipeCategory::class;
    }

    /**
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
        $model = $this->model()->create($collection);

        $this->setTranslations($model, $collection);

        if (isset($collection['image'])) {
            $model->update(['image' => $collection['image']]);
        }
        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
    }

    /**
     * @param $collection
     * @param $id
     * @return array
     */
    public function update($collection, $id): array
    {
        $model = $this->model()->find($id);

        if ($model) {
            $model->update($collection);
            $this->setTranslations($model, $collection);
            if (isset($collection['image'])) {
                $model->update(['image' => $collection['image']]);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @param array $ids
     * @return array
     */
    public function delete(array $ids): array
    {
        $items = $this->model()->whereDoesntHave('recipes')->whereDoesntHave('child')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_510];
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value) {
            $model->translation()->create([
                'title' => $value,
                'description' => $collection['description'][$index] ?? null,
                'locale' => $index,
            ]);
        }
    }
}
