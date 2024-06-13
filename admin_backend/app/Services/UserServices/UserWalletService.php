<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CoreService;
use Exception;
use Illuminate\Support\Str;

class UserWalletService extends CoreService
{
    protected function getModelClass(): string
    {
        return Wallet::class;
    }

    public function create($user) {

        try {
            $this->model()
                ->withTrashed()
                ->updateOrCreate(['user_id' => $user->id], [
                    'uuid'          => Str::uuid(),
                    'user_id'       => $user->id,
                    'currency_id'   => Currency::whereDefault(1)->pluck('id')->first(),
                    'price'         => 0,
                    'deleted_at'    => null,
                ]);

        } catch (Exception $exception) {}

        return $user->loadMissing('wallet');
    }

    public function update($user, $array): array
    {
        $userWalletPrice = $user->wallet ? $user->wallet->price : 0;
            $user->wallet()->update([
                'price' => $userWalletPrice + $array['price'],
            ]);
            if ($user->wallet)
                $this->historyCreate($user->wallet, $array);
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    public function historyCreate($wallet, $array){
        $wallet->histories()->create([
            'uuid' => Str::uuid(),
            'transaction_id' => $array['transaction_id'] ?? null,
            'type' => $array['type'] ?? 'topup',
            'price' => $array['price'],
            'note' => $array['note'] ?? null,
            'created_by' => auth('sanctum')->id(),
        ]);
    }
}
