<?php

namespace App\Services\SubscriptionService;

use App\Helpers\ResponseError;
use App\Models\ShopSubscription;
use App\Models\Subscription;
use App\Services\CoreService;
use Exception;
use Illuminate\Database\Eloquent\Model;

class SubscriptionService extends CoreService
{
    protected function getModelClass(): string
    {
        return Subscription::class;
    }

    public function update(int $id, $data): array
    {
        $subscription = Subscription::find($id);
        try {
            $subscription->update([
                'active' => $data->active,
                'price' => $data->price,
                'month' => $data->month,
                'type' => $data->type,
            ]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $subscription];

        } catch (\Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502];
        }
    }

    public function subscriptionAttach(Subscription $subscription, int $shopId): Model|ShopSubscription
    {
        return ShopSubscription::create([
            'shop_id'           => $shopId,
            'subscription_id'   => $subscription->id,
            'expired_at'        => now()->addMonths($subscription->month),
            'price'             => $subscription->price,
            'type'              => data_get($subscription, 'type', 'order'),
            'active'            => 0,
        ]);
    }
}
