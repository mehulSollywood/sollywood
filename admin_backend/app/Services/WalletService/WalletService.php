<?php

namespace App\Services\WalletService;


use App\Helpers\ResponseError;
use App\Models\User;
use App\Models\WalletRequest;
use App\Services\CoreService;

class WalletService extends CoreService
{

    protected function getModelClass(): string
    {
        return WalletRequest::class;
    }



    public function changeStatus(string $uuid, string $status = null): array
    {
        $walletHistory = $this->model()->firstWhere('uuid', $uuid);
        if ($walletHistory) {
            if ($walletHistory->status == 'processed') {
                $walletHistory->update(['status' => $status]);

                if ($status == 'rejected' || $status == 'canceled') {
                    $walletHistory->wallet()->update(['price' => $walletHistory->wallet->price + $walletHistory->price]);
                }
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
