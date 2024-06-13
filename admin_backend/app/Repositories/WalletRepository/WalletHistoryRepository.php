<?php

namespace App\Repositories\WalletRepository;

use App\Models\WalletHistory;
use App\Repositories\CoreRepository;

class WalletHistoryRepository extends CoreRepository
{

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return WalletHistory::class;
    }

    public function walletHistoryPaginate($perPage, $array = [])
    {
        $array['wallet_uuid'] = data_get(auth('sanctum')->user(), 'wallet.uuid');

        return $this->model()->with('author', 'user')
            ->when(data_get($array, 'wallet_uuid'),function ($q) use ($array){
                $q->where('wallet_uuid', data_get($array, 'wallet_uuid'));
            })
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            })
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', $array['type']);
            })
            ->orderBy('id','desc')
            ->paginate($perPage);
    }

    // route v1/dashboard/admin/users/{uuid}/wallets/history ищет по переданному uuid а не по своему auth(uuid)
    public function walletHistoryByUuIdPaginate($perPage, $data = [])
    {
        return $this->model()->with('author', 'user')
            ->when(data_get($data, 'wallet_uuid'), function ($q) use ($data) {
                $q->where('wallet_uuid', data_get($data, 'wallet_uuid'));
            })
            ->when(data_get($data, 'status'), function ($q) use ($data) {
                $q->where('status', data_get($data, 'status'));
            })
            ->when(data_get($data, 'type'), function ($q) use ($data) {
                $q->where('type', data_get($data, 'type'));
            })
            ->orderBy('id','desc')
            ->paginate($perPage);
    }
}
