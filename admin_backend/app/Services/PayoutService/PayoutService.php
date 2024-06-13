<?php

namespace App\Services\PayoutService;

use App\Helpers\ResponseError;
use App\Models\Payout;
use App\Models\User;
use App\Models\WalletHistory;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;
use Throwable;

class PayoutService extends CoreService
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $data['status'] = 'pending';
            $this->model()->create($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'message' => ResponseError::ERROR_501, 'code' => ResponseError::ERROR_501];
        }
    }

    public function update(Payout $payout, array $data): array
    {
        try {
            $payout->update($data);

            return [
                'status'  => true,
                'message' => ResponseError::NO_ERROR,
            ];

        } catch (Throwable $e) {

            $this->error($e);

            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    public function destroy(?array $ids = []) {

        foreach (Payout::find(is_array($ids) ? $ids : []) as $payout) {

            if ($payout->created_by !== auth('sanctum')->id()) {
                continue;
            }

            $payout->delete();
        }

    }

    public function statusChange(?int $id = null, ?string $status = null): array
    {

        if (empty($id) || !in_array($status, Payout::STATUSES)) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }

        $payout = Payout::find($id);

        if (empty($payout)) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        if ($payout->status == Payout::STATUS_ACCEPTED) {
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_400,
                'message'   => 'Payout already ' . Payout::STATUS_ACCEPTED
            ];
        }

        if ($status == Payout::STATUS_CANCELED){

            $payout->update([
                'status'        => $status,
                'approved_by'   => auth('sanctum')->id(),
            ]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        if (empty($payout->createdBy)) {
            return ['status' => false, 'code' => ResponseError::ERROR_404, 'message' => 'User not found'];
        }
        /** @var User $user */

        $user = auth('sanctum')->user();
        $authWallet = $user->wallet;

        if (data_get($authWallet, 'price', 0) < $payout->price) {
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_109,
                'message'   => 'Insufficient wallet balance'
            ];
        }

        $payout->update([
            'status'        => $status,
            'approved_by'   => auth('sanctum')->id(),
        ]);


        $createdByNote =
            "Receipts for {$payout->createdBy->firstname}/{$payout->createdBy->lastname}";

        $approveBydNote =
            "Payment for {$payout->approvedBy->firstname}/{$payout->approvedBy->lastname}";

        if (
            $status === Payout::STATUS_ACCEPTED &&
            optional($payout->payment)->tag !== 'cash' &&
            optional($payout->createdBy)->wallet
        ) {

            (new WalletHistoryService)->create($authWallet, [
                'type'      => 'topup',
                'price'     => $payout->price,
                'note'      => $createdByNote,
                'status'    => WalletHistory::PAID,
                'user'      => $payout->createdBy
            ]);

            $authWallet->update([
                'price' => $authWallet->price - $payout->price,
            ]);

            (new WalletHistoryService)->create($authWallet,[
                'type'      => 'withdraw',
                'price'     => $payout->price,
                'note'      => $approveBydNote,
                'status'    => WalletHistory::PAID,
                'user'      => $payout->approvedBy
            ]);
        }

        $payout->createdBy->wallet->createTransaction([
            'price'                 => $payout->price,
            'user_id'               => $payout->created_by,
            'payment_sys_id'        => $payout->payment_id,
            'payment_trx_id'        => null,
            'note'                  => $createdByNote,
            'perform_time'          => now(),
            'status_description'    => $createdByNote
        ]);

        $payout->approvedBy->wallet->createTransaction([
            'price'                 => $payout->price,
            'user_id'               => $payout->approved_by,
            'payment_sys_id'        => $payout->payment_id,
            'payment_trx_id'        => null,
            'note'                  => $approveBydNote,
            'perform_time'          => now(),
            'status_description'    => $approveBydNote
        ]);

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }
}
