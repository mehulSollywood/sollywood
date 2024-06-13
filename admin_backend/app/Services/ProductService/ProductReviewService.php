<?php

namespace App\Services\ProductService;

use App\Helpers\ResponseError;
use App\Models\ShopProduct;
use App\Services\CoreService;

class ProductReviewService extends CoreService
{

    protected function getModelClass(): string
    {
        return ShopProduct::class;
    }

    public function addReview($uuid, $collection): array
    {
        /** @var ShopProduct $product */
        $product = $this->model()->firstWhere('uuid', $uuid);

        if ($product) {

            $exist = $product->reviews()->where('user_id', auth('sanctum')->id())->exists();

            if ($exist) {
                return ['status' => false, 'code' => ResponseError::ERROR_512];
            }

            $product->addReview($collection);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $product];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
