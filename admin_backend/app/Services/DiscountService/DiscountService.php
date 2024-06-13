<?php

namespace App\Services\DiscountService;

use App\Helpers\ResponseError;
use App\Models\Discount;
use App\Services\CoreService;

class DiscountService extends CoreService
{

    protected function getModelClass(): string
    {
        return Discount::class;
    }

    public function create($collection): array
    {
        $discount = $this->model()->create($collection);
        if ($discount) {
            $discount->update(['img' => $collection['images'][0]]);
            $discount->uploads($collection['images']);
            if (count($collection['products']) > 0) {
                $discount->products()->attach($collection['products']);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $discount];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];
    }

    /**
     *
     */
    public function update($id, $collection): array
    {
        $discount = $this->model()->firstWhere(['id' => $id, 'shop_id' => $collection['shop_id']]);
        if ($discount) {
            $discount->update($collection);
            $discount->update(['img' => $collection['images'][0]]);
            $discount->uploads($collection['images']);
            if (count($collection['products']) > 0) {
                $discount->products()->detach();
                $discount->products()->attach($collection['products']);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $discount];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


}
