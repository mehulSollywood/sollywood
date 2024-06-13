<?php

namespace App\Services\WalletRequestService;

use App\Helpers\ResponseError;
use App\Models\PushNotification;
use App\Models\User;
use App\Models\WalletHistory;
use App\Models\WalletRequest;
use App\Services\CoreService;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Traits\Notification;
use DB;
use Exception;
use Throwable;

class WalletRequestService extends CoreService
{
    use Notification;

    protected function getModelClass(): string
    {
        return WalletRequest::class;
    }

    public function create($collection): array
    {
        $requestUser = User::where('id', auth('sanctum')->user()->id)->first();

        $responseUser = User::where('phone', $collection['user_phone'])->first();

        $model = $this->model->create([
            'request_user_id' => $requestUser->id,
            'response_user_id' => $responseUser->id,
            'price' => $collection['price'],
            'status' => WalletRequest::PENDING,
            'message' => $collection['message'],
        ]);

        $data = [
            'type' => PushNotification::WALLET_REQUEST,
            'id' => $model->id,
        ];

        $this->sendNotification(
            [data_get($responseUser->tokens, 0)],
            $requestUser->phone . " sent you a wallet request",
            data_get($model, 'id'),
            $data,
            [$responseUser->id]
        );
        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    public function update($collection, $id): array
    {
        $model = $this->model->find($id);

        if ($model) {
            if ($model->status == WalletRequest::PENDING) {
                $model->update([
                    'price' => $collection['price'],
                    'status' => WalletRequest::PENDING,
                    'message' => $collection['message'],
                ]);

                return ['status' => true, 'code' => ResponseError::NO_ERROR];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_255];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function destroy(string $id): array
    {
        $item = $this->model()->where('id', $id)->first();
        if ($item) {
            $item->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @throws Throwable
     */
    public function changeStatus(array $collection, int $id): array
    {
        /** @var WalletRequest $model */

        $model = $this->model->find($id);

        if ($model) {

            if ($model->status == WalletRequest::REJECTED || $model->status == WalletRequest::APPROVED) {
                return ['status' => false, 'code' => ResponseError::ERROR_255];
            }

            if ($collection['status'] == WalletRequest::REJECTED) {
                $model->update([
                    'status' => $collection['status']
                ]);

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
            }

            $user = User::find(auth('sanctum')->user()->id);

            if ($model->response_user_id == $user->id) {

                if ($user->wallet?->price < $model->price) {
                    return ['status' => false, 'code' => ResponseError::ERROR_109];
                }

                try {
                    DB::beginTransaction();

                    (new WalletHistoryService())->create($user, [
                        'type' => WalletHistory::WITHDRAW,
                        'price' => $model->price,
                        'created_by' => $user->id,
                        'status' => WalletHistory::PAID,
                    ]);

                    (new WalletHistoryService())->create($model->requestUser, [
                        'type' => WalletHistory::TOP_UP,
                        'price' => $model->price,
                        'created_by' => $model->request_user_id,
                        'status' => WalletHistory::PAID,
                    ]);

                    $model->update([
                        'status' => $collection['status']
                    ]);

                    $data = [
                        'type' => PushNotification::WALLET_REQUEST,
                        'id' => $model->id,
                        'status' => $model->status
                    ];

                    $this->sendNotification(
                        [data_get($user->tokens, 0)],
                        $user->phone . " change wallet request status to " . $model->status,
                        data_get($model, 'id'),
                        $data,
                        [$user->id]
                    );

                    DB::commit();
                } catch (Exception $exception) {
                    DB::rollBack();
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];

            }

            return ['status' => false, 'code' => ResponseError::ERROR_255];

        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
