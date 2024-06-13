<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\ShopPaymentResource;
use App\Repositories\PaymentRepository\PaymentRepository;
use App\Repositories\ShopPaymentRepository\ShopPaymentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends RestBaseController
{
    public function __construct(protected ShopPaymentRepository $shopPaymentRepository,protected PaymentRepository $paymentRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $payments = $this->shopPaymentRepository->paginate($request->perPage ?? 15,$request->all(), $request->shop_id);
        return ShopPaymentResource::collection($payments);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function adminPayment(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $payments = $this->paymentRepository->paginate($request->perPage ?? 15,$request->all());
        return PaymentResource::collection($payments);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $payment = $this->shopPaymentRepository->getById($id);
        if ($payment && $payment->status){
            return $this->successResponse(__('web.payment_found'), ShopPaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
