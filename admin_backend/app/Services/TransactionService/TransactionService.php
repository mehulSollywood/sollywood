<?php

namespace App\Services\TransactionService;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\ParcelOrder;
use App\Models\ShopPayment;
use App\Models\ShopSubscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CoreService;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class TransactionService extends CoreService
{

    protected function getModelClass(): string
    {
        return Transaction::class;
    }

    public function orderTransaction(int $id, $collection, $class = Order::class): array
    {
        $order = $class::find($id);
        /** @var Order $order */
        if ($order) {
            if ($class == ParcelOrder::class){
                $price = $order->total_price;
            }else{
                $price = $order->price;
            }
            $payment = $this->checkPayment($collection['payment_sys_id'], $order);

            if ($payment['status']) {
                $data = [
                    'price' => $price,
                    'user_id' => $order->user_id,
                    'payment_sys_id' => $collection['payment_sys_id'],
                    'payment_trx_id' => $collection->payment_trx_id ?? null,
                    'note' => $order->id,
                    'perform_time' => now(),
                    'status_description' => 'Transaction for order #' . $order->id
                ];

                $transaction = $order->createTransaction($data);

                if (isset($payment['wallet'])){
                    $user = User::find($order->user_id);
                        $this->walletHistoryAdd($user, $transaction, $order);
                    }

                if (isset($payment['cash'])){
                    $transaction->update([
                        'status' => Transaction::DEBIT,
                        'request' => Transaction::REQUEST_WAITING,
                    ]);
                }
            }

            if (!Cache::has('project.status') || Cache::get('project.status')->active != 1){
                return ['status' => false, 'code' => ResponseError::ERROR_403];
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $order];
        }  else {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
    }

    public function walletTransaction(int $id, $collection): array
    {
        $wallet = Wallet::find($id);
        $collection->user_id = auth('sanctum')->id();
        if ($wallet) {

            $wallet->createTransaction([
                    'price' => $collection->price,
                    'user_id' => $collection->user_id,
                    'payment_sys_id' => $collection->payment_sys_id,
                    'payment_trx_id' => $collection->payment_trx_id ?? null,
                    'note' => $wallet->id,
                    'perform_time' => now(),
                    'status_description' => 'Transaction for wallet #' . $wallet->id
                ]);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $wallet];
        }  else {
            return ['status' => true, 'code' => ResponseError::ERROR_404];
        }
    }

    public function subscriptionTransaction(int $id, $collection): array
    {
        $subscription = ShopSubscription::find($id);

        if (!$subscription) {

            return ['status' => false, 'code' => ResponseError::ERROR_404];

        } else if($subscription->active) {

            return ['status' => false, 'code' => ResponseError::ERROR_208];
        }

        $payment = $this->checkPayment($collection->payment_sys_id, request()->merge([
            'user_id' => auth('sanctum')->id(),
            'price' => $subscription->price,
        ]));

        if ($payment['status']) {

            $transaction = $subscription->createTransaction([
                'price' => $subscription->price,
                'user_id' => auth('sanctum')->id(),
                'payment_sys_id' => $collection->payment_sys_id,
                'payment_trx_id' => $collection->payment_trx_id ?? null,
                'note' => $subscription->id,
                'perform_time' => now(),
                'status_description' => 'Transaction for Subscription #' . $subscription->id
            ]);

            if (isset($payment['wallet'])) {
                $subscription->update(['active' => 1]);
                $this->walletHistoryAdd(auth('sanctum')->user(), $transaction, $subscription, 'Subscription');
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $subscription];
        }

        return $payment;

    }

    private function checkPayment(int $id, $model): array
    {
        $payment = ShopPayment::where('status', 1)->find($id);
        if ($payment) {
            if ($payment->payment->tag == 'wallet') {

                $user = User::find($model->user_id);
                if ($user->wallet)
                {
                    /** @var Order|Model $model */

                    if ($user && $user->wallet->price >= $model->price) {
                        $user->wallet()->update(['price' => $user->wallet->price - $model->price]);
                        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'wallet' => $user->wallet];
                    } else {
                        return ['status' => false, 'code' => ResponseError::ERROR_109];
                    }
                }

            }
            if ($payment->payment->tag == 'cash'){
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'cash' => true];
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR];

        } else {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
    }

    private function walletHistoryAdd($user, $transaction, $model, $type = 'Order')
    {
        $user->wallet->histories()->create([
            'uuid' => Str::uuid(),
            'transaction_id' => $transaction->id,
            'type' => 'withdraw',
            'price' => $transaction->price,
            'note' => "Payment $type #$model->id via Wallet" ,
            'status' => "paid",
            'created_by' => $transaction->user_id,
        ]);

        $transaction->update(['status' => 'paid']);
    }

    /**
     * @throws Throwable
     */
    public function payDebit(User $user, Order $order)
    {
        if ($user?->wallet?->price > $order->price){
            try {
                DB::beginTransaction();

                $user->wallet->update(['price' => $user->wallet->price - $order->price]);

                $data = [
                    'price' => $order->price,
                    'user_id' => $order->user_id,
                    'payment_sys_id' => $order->transaction->payment_sys_id,
                    'payment_trx_id' => $order->transaction->payment_trx_id ?? null,
                    'note' => $order->transaction->id,
                    'perform_time' => now(),
                    'status_description' => 'Transaction for debit transaction #' . $order->transaction->id,
                    'status' => Transaction::PAID
                ];

                $transaction = $order->transaction->createTransaction($data);

                $this->walletHistoryAdd($user,$transaction,$transaction);

                $order->transaction->update([
                    'status' => Transaction::PAID
                ]);

                DB::commit();

            }catch (Throwable $t){
                DB::rollBack();
            }
        }else{
            $order->transaction->update(['request' => Transaction::REQUEST_WAITING]);
        }
    }
}
