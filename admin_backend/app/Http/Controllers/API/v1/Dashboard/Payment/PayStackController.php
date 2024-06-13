<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use Redirect;
use Throwable;
use App\Models\Currency;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Payment\StripeRequest;
use App\Services\PaymentService\PayStackService;
use App\Http\Requests\Payment\SubscriptionRequest;

class PayStackController extends Controller
{
    use ApiResponse;

    public function __construct(private PayStackService $service)
    {
        parent::__construct();
    }

    /**
     * process transaction.
     *
     * @param StripeRequest $request
     * @return JsonResponse
     */
    public function orderProcessTransaction(StripeRequest $request): JsonResponse
    {
        try {
            $result = $this->service->orderProcessTransaction($request->all());

            return $this->successResponse('success', $result);
        } catch (Throwable $e) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => $e->getMessage()]);
        }
    }

    /**
     * process transaction.
     *
     * @param SubscriptionRequest $request
     * @return JsonResponse
     */
    public function subscriptionProcessTransaction(SubscriptionRequest $request): JsonResponse
    {
        $shop       = auth('sanctum')->user()?->shop ?? auth('sanctum')->user()?->moderatorShop;

        $currency   = Currency::currenciesList()->where('active', 1)->where('default', 1)->first()?->title;

        if (empty($shop)) {
            return $this->onErrorResponse([
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        if (empty($currency)) {
            return $this->onErrorResponse([
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        try {
            $result = $this->service->subscriptionProcessTransaction($request->all(), $shop, $currency);

            return $this->successResponse('success', $result);
        } catch (Throwable $e) {
            $this->error($e);
            return $this->onErrorResponse([
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)
            ]);
        }

    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function orderResultTransaction(Request $request): RedirectResponse
    {
        $orderId = (int)$request->input('order_id');

        $to = config('app.front_url') . "orders/$orderId";

        return Redirect::to($to);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function subscriptionResultTransaction(Request $request): RedirectResponse
    {
        $subscription = Subscription::find((int)$request->input('subscription_id'));

        $to = config('app.front_url') . "seller/subscriptions/$subscription->id";

        return Redirect::to($to);
    }

    /**
     * @return void
     */
    public function paymentWebHook(): void
    {
        $ips = ['52.31.139.75', '52.49.173.169', '52.214.14.220']; // but I got 213.230.97.92

        $status = request()->input('event');

        $status = match ($status) {
            'charge.success'    => 'paid',
            default             => 'progress',
        };

        $token = request()->input('data.reference');

        $this->service->afterHook($token, $status);
    }
}
