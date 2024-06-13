<?php

namespace App\Services\ShopProductService;

use App\Helpers\ResponseError;
use App\Models\ShopProduct;
use App\Services\CoreService;

class ShopProductService extends CoreService
{

    protected function getModelClass(): string
    {
        return ShopProduct::class;
    }

    public function create($collection): array
    {
        $data = $this->model()->create($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $data];
    }

    public function update($collection, $id): array
    {
        $model = $this->model()->find($id);

        if ($model) {

            $model->update($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function decrementStocksQuantity(array $products)
    {
        foreach ($products as $product) {
            $this->model()->find($product['shop_product_id'])->decrement('quantity', $product['qty']);
        }
    }

    public function incrementStocksQuantity($products)
    {
        foreach ($products as $product) {
            $this->model()->find($product['shop_product_id'])->increment('quantity', $product['qty']);
        }
    }

}
