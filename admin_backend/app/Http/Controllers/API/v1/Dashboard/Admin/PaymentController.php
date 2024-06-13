<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Payment;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Payment\UpdateRequest;
use App\Repositories\PaymentRepository\PaymentRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends AdminBaseController
{

    public function __construct(protected PaymentRepository $paymentRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $payments = $this->paymentRepository->paymentsList($request->all());
        return PaymentResource::collection($payments);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        if ($payment) {
            return $this->successResponse(__('web.payment_found'), PaymentResource::make($payment->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse|AnonymousResourceCollection
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        /** @var Payment $payment */

        if ($payment) {
            $result = $payment->update([
                'client_id' => $request->client_id ?? null,
                'secret_id' => $request->secret_id ?? null,
                'sandbox' => $request->sandbox ?? 0,
                'merchant_email' => $request->merchant_email ?? null,
                'payment_key' => $request->payment_key ?? null,
            ]);
            if ($result) {
                $payment->translations()->delete();
                foreach ($request->title as $index => $title) {
                    $payment->translation()->create([
                        'locale' => $index,
                        'title' => $title,
                    ]);
                }
            }
            return $this->successResponse(__('web.record_has_been_successfully_updated'), PaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Set Model Active.
     *
     * @param  int  $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function setActive(int $id): JsonResponse|AnonymousResourceCollection
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        if ($payment) {
            $payment->update(['active' => !$payment->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), PaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
