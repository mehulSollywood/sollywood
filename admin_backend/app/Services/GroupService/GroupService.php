<?php

namespace App\Services\GroupService;

use App\Helpers\ResponseError;
use App\Models\Group;
use App\Services\CoreService;

class GroupService extends CoreService
{

    protected function getModelClass(): string
    {
        return Group::class;
    }

    public function create($collection): array
    {

        $model = $this->model()->create($collection);

        $this->setTranslations($model, $collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
    }

    public function update($id,$collection): array
    {
        $model = $this->model()->find($id);

        if ($model) {
            $model->update($collection);
            $this->setTranslations($model, $collection);
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
        $items = $this->model()->whereDoesntHave('shops')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value) {
            $model->translation()->create([
                'title' => $value,
                'locale' => $index,
            ]);
        }
    }
}
