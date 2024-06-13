<?php

namespace App\Services\ShopPaymentService;

use App\Helpers\ResponseError;
use App\Models\Payment;
use App\Models\Settings;
use App\Models\ShopPayment;
use App\Services\CoreService;

class ShopPaymentService extends CoreService
{

    protected function getModelClass(): string
    {
        return ShopPayment::class;
    }

    public function create($collection): array
    {
        $paymentItems = $this->checkPaymentType($collection);

        $model = $this->model()->create(array_merge((array)$paymentItems, $collection));

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];

    }

    public function update($collection, $id): array
    {

        $model = $this->model()->find($id);

        if ($model)
        {
            $paymentItems = $this->checkPaymentType($collection);

            $model = $model->update(array_merge((array)$paymentItems, $collection));
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    private function checkPaymentType($collection): bool
    {
        $settings = Settings::where('key','payment_type')->where('value','admin')->first();

        if ($settings){

            $payment = Payment::find($collection['payment_id']);
            $collection['client_id'] = $payment->client_id;
            $collection['secret_id'] = $payment->secret_id;
            $collection['merchant_email'] = $payment->merchant_email;
            $collection['payment_key'] = $payment->payment_key;
            return $collection;
        }
        return false;
    }

}
