<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\DeleteAllRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ShopPayment\StoreRequest;
use App\Http\Requests\ShopPayment\UpdateRequest;
use App\Services\ShopPaymentService\ShopPaymentService;
use App\Repositories\PaymentRepository\PaymentRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ShopPaymentRepository\ShopPaymentRepository;

class ShopPaymentController extends SellerBaseController
{

    public function __construct(
        protected ShopPaymentRepository $shopPaymentRepository,
        protected ShopPaymentService    $shopPaymentService,
        protected PaymentRepository     $paymentRepository
    )
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        return $this->shopPaymentRepository->paginate($request->perPage, $request->all(), $this->shop->id);
    }

    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $shopPayment = $this->shopPaymentRepository->getById($id, $this->shop->id);

        if ($shopPayment) {
            return $this->successResponse(__('web.coupon_found'), $shopPayment);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function store(StoreRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();
        $collection['shop_id'] = $this->shop->id;
        $result = $this->shopPaymentService->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), $result['data']);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function update(UpdateRequest $request, $id): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $result = $this->shopPaymentService->update($collection, $id);

        if ($result['status']) {

            return $this->successResponse(__('web.record_successfully_updated'), $result['data']);

        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();
        $result = $this->shopPaymentService->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function allPayment(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $payment = $this->paymentRepository->shopPaymentNonExistPaginate($this->shop->id, $request->perPage ?? 10);
        return $this->successResponse(__('web.record_successfully_updated'), PaymentResource::collection($payment));
    }

}
