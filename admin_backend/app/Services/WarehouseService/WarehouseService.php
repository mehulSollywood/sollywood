<?php

namespace App\Services\WarehouseService;

use App\Helpers\ResponseError;
use App\Models\ShopProduct;
use App\Models\Warehouse;
use App\Services\CoreService;
use Exception;

class WarehouseService extends CoreService
{
    protected function getModelClass(): string
    {
        return Warehouse::class;
    }

    public function create($collection): array
    {
        try {

            if ($collection['type'] == Warehouse::TYPE_INCOME) {

                $shopProduct = ShopProduct::find($collection['shop_product_id']);

                $shopProduct?->increment('quantity', $collection['quantity']);
            }

            if ($collection['type'] == Warehouse::TYPE_OUTCOME) {

                $shopProduct = ShopProduct::find($collection['shop_product_id']);

                if ($shopProduct && $shopProduct->quantity < $collection['quantity']) {
                    return ['status' => false, 'code' => ResponseError::ERROR_256];
                }

                $shopProduct?->decrement('quantity', $collection['quantity']);
            }

            $model = $this->model()->create($collection);

            if ($model) {
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function destroy(int $id)
    {

        $model = $this->model->find($id);
        if ($model) {

            if ($model->type == Warehouse::TYPE_INCOME) {

                $shopProduct = ShopProduct::find($model->shop_product_id);

                $shopProduct?->increment('quantity', $model->quantity);

                $model->delete();
            }

            if ($model->type == Warehouse::TYPE_OUTCOME) {

                $shopProduct = ShopProduct::find($model->shop_product_id);

                if ($shopProduct && $shopProduct->quantity < $model->quantity) {
                    return ['status' => false, 'code' => ResponseError::ERROR_256];
                }

                $shopProduct?->decrement('quantity', $model->quantity);

                $model->delete();
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR];

        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

}
