<?php

namespace App\Services\BranchService;

use App\Helpers\ResponseError;
use App\Models\Branch;
use App\Services\CoreService;

class BranchService extends CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Branch::class;
    }

    public function create($collection, $shop_id): array
    {
        $collection['shop_id'] = $shop_id;
        $coupon = $this->model()->create($collection);
        $this->setTranslations($coupon, $collection);
        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $coupon];

    }

    /**
     * @param int $id
     * @param $collection
     * @param $shop_id
     * @return array
     */
    public function update(int $id, $collection, $shop_id): array
    {
        $collection['shop_id'] = $shop_id;
        $coupon = $this->model()->find($id);
        if ($coupon) {
            $coupon->update($collection);
            $this->setTranslations($coupon, $collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $coupon];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value) {
            $model->translation()->create([
                'title' => $value,
                'address' => $collection['address'][$index] ?? null,
                'locale' => $index,
            ]);
        }
    }
}
