<?php

namespace App\Services\DeliveryService;

use App\Helpers\ResponseError;
use App\Models\Delivery;
use App\Services\CoreService;

class DeliveryService extends CoreService
{

    protected function getModelClass(): string
    {
        return Delivery::class;
    }

    /**
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
        $default = $this->model()->create($this->setDeliveryParam($collection));
        if ($default) {
            $this->setTranslations($default, $collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $default];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];
    }

    /**
     * @param $id
     * @param $collection
     * @return array
     */
    public function update($id, $collection): array
    {
        $default = $this->model()->find($id);
        if ($default) {
            $default->update($this->setDeliveryParam($collection));
            $this->setTranslations($default, $collection);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $default];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


    /**
     * @param $model
     * @param $collection
     */
    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();

        foreach ($collection->title as $index => $value) {
            $model->translation()->create([
                'title' => $value,
                'locale' => $index,
            ]);
        }
    }

    /**
     * @param $collection
     * @return array
     */
    public function setDeliveryParam($collection): array
    {
        return [
            'shop_id' => $collection->shop_id ?? null,
            'type' => $collection->type ?? 'standard',
            'price' => $collection->price ?? 0,
            'times' => $collection->times ? explode(',', $collection->times) : null,
            'note' => $collection->note ?? null,
            'default' => $collection->default ?? 0,
            'active' => $collection->active ?? 0,
        ];
    }
}
