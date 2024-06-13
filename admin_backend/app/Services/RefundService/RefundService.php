<?php

namespace App\Services\RefundService;

use App\Helpers\ResponseError;
use App\Http\Requests\Refund\StatusUpdateRequest;
use App\Models\Recipe;
use App\Models\Refund;
use App\Models\Transaction;
use App\Services\CoreService;

class RefundService extends CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Refund::class;
    }

    public function create($collection): array
    {
        $refund = $this->model()->create($collection);

        if ($refund && isset($collection['images'])) {
            $this->updateImage($refund,$collection);
        }
        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $refund];
    }

    /**
     * @param int $id
     * @param $collection
     * @return array
     */
    public function update($collection, int $id): array
    {
        $refund = $this->model->find($id);
        if ($refund)
        {
            $refund->update($collection);
            if (isset($collection['images'])) {
                $this->updateImage($refund,$collection);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $refund];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];

    }

    /**
     * @param $collection
     * @param int $id
     * @return array
     */
    public function statusChange($collection, int $id): array
    {
        $refund = $this->model->find($id);

        if ($refund->status == Transaction::PAID)
            return ['status' => false, 'code' => ResponseError::ERROR_252];

        if (!$refund)
        {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        $refund->update([
            'status' => $collection['status']
        ]);

        if ($refund->status == 'canceled' && $refund->order->transaction()->where('status',Transaction::PAID)->first())
        {
            $refund->user->wallet()->update(['price' => $refund->user->wallet->price + $refund->order->price]);
        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];

    }



}
