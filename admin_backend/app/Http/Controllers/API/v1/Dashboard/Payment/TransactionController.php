<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\ParcelOrder;
use App\Helpers\ResponseError;
use App\Models\PaymentProcess;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderResource;
use App\Http\Resources\WalletResource;
use App\Http\Resources\SubscriptionResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\Transaction\StoreRequest;
use App\Http\Requests\Payment\TransactionUpdateRequest;
use App\Services\TransactionService\TransactionService;

class TransactionController extends PaymentBaseController
{

    public function __construct(protected Transaction $model,protected TransactionService $transactionService)
    {
        parent::__construct();
    }

    public function store(string $type, int $id, StoreRequest $request): JsonResponse
    {
        switch ($type) {
            case 'order':
                $collection = $request->validated();
                $result = $this->transactionService->orderTransaction($id, $collection);
                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'), OrderResource::make($result['data']));
                }
                return $this->errorResponse(
                    $result['code'], __('errors.' . $result['code'], [], $this->language),
                    Response::HTTP_NOT_FOUND
                );
            case 'subscription':
                $result = $this->transactionService->subscriptionTransaction($id, $request);

                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'), SubscriptionResource::make($result['data']));
                }

                return $this->errorResponse(
                    $result['code'], __('errors.' . $result['code'], [], $this->language),
                    Response::HTTP_NOT_FOUND
                );
            case 'parcel-order':
                $result = (new TransactionService())->orderTransaction($id, $request,ParcelOrder::class);
                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'), SubscriptionResource::make($result['data']));
                } else {
                    return $this->errorResponse(
                        $result['code'], __('errors.' . $result['code'], [], $this->language),
                        Response::HTTP_BAD_REQUEST
                    );
                }
            default:
                $result = $this->transactionService->walletTransaction($id, $request);

                if ($result['status']) {
                    return $this->successResponse(__('web.record_successfully_created'), WalletResource::make($result['data']));
                }

                return $this->errorResponse(
                    $result['code'], __('errors.' . $result['code'], [], $this->language),
                    Response::HTTP_NOT_FOUND
                );
        }
    }

    public function updateStatus(int $id, TransactionUpdateRequest $request): JsonResponse
    {
        /** @var Order $order */
        $order = Order::with('transactions')->find($id);

        if (!$order) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_404,
                'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        if (!$order->transaction) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_501,
                'message'   => 'Transaction is not created'
            ]);
        }

        $paymentProcess = PaymentProcess::find($request->input('token'));

        if (empty($paymentProcess) && !in_array($order->transaction->paymentSystem?->payment?->tag, ['cash', 'wallet'])) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'Order not paid']);
        }

        /** @var Transaction $transaction */
        $order->transaction->update([
            'status' => $request->input('status')
        ]);

        $paymentProcess?->delete();

        return $this->successResponse('Success', $order->fresh('transactions'));
    }


}
