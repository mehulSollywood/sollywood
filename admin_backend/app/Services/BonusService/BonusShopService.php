<?php

namespace App\Services\BonusService;

use App\Helpers\ResponseError;
use App\Models\BonusShop;
use App\Services\CoreService;

class BonusShopService extends CoreService
{

    protected function getModelClass(): string
    {
        return BonusShop::class;
    }

    public function create($collection): array
    {
        $bonusShop = $this->model()->create($collection);
        if ($bonusShop) {
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $bonusShop];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];
    }

    public function update(int $id, $collection): array
    {
        $brand = $this->model()->find($id);
        if ($brand) {
            $brand->update($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $brand];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function statusChange(int $id): array
    {
        $bonusShop = $this->model()->find($id);
        if ($bonusShop) {
            $bonusShop->update(['status' => !$bonusShop->status]);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $bonusShop];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];

    }
}
