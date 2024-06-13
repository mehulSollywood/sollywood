<?php

namespace App\Services\ShopWorkingDayService;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Models\ShopWorkingDay;
use App\Services\CoreService;
use Throwable;

class ShopWorkingDayService extends CoreService
{
    protected function getModelClass(): string
    {
        return ShopWorkingDay::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $exists = [];

            foreach (data_get($data, 'dates', []) as $date) {

                $exist = ShopWorkingDay::where([
                    ['shop_id', data_get($data, 'shop_id')],
                    ['day',     data_get($date, 'day')]
                ])->exists();

                if (!$exist) {
                    $this->model()->create($date + ['shop_id' => data_get($data, 'shop_id')]);
                    continue;
                }

                $exists[] = data_get($date, 'day');
            }

            if (count($exists) > 0) {
                return [
                    'status'  => false,
                    'message' => 'already exist days: ' . implode(', ', $exists),
                ];
            }

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];
        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => $e, 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(int $shopId, array $data): array
    {
        try {

            Shop::find($shopId)->workingDays()->delete();

            foreach (data_get($data, 'dates', []) as $date) {

                ShopWorkingDay::create($date + ['shop_id' => $shopId]);

            }

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e];
        }
    }

    public function destroy(?array $ids = [], ?int $shopId = null) {

        $shopWorkingDays = ShopWorkingDay::when($shopId, fn($q, $shopId) => $q->where('shop_id', $shopId))->find(is_array($ids) ? $ids : []);

        foreach ($shopWorkingDays as $shopWorkingDay) {
            $shopWorkingDay->delete();
        }

    }
}
