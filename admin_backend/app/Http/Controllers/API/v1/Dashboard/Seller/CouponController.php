<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CouponResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\CategoryCreateRequest;
use App\Services\CouponService\CouponService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\CouponRepository\CouponRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CouponController extends SellerBaseController
{

    public function __construct(protected CouponRepository $couponRepository,protected CouponService $couponService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $coupons = $this->couponRepository->couponsList($request->all());
        return CouponResource::collection($coupons);
    }

    /**
     * Display a listing of the resource.
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $coupons = $this->couponRepository->couponsPaginate($request->perPage, $this->shop->id);
        return CouponResource::collection($coupons);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->couponService->create($request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), CouponResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $coupon = $this->couponRepository->couponById($id, $this->shop->id);
        if ($coupon) {
            $coupon->load('translations');
            return $this->successResponse(__('web.coupon_found'), CouponResource::make($coupon));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param CategoryCreateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $result = $this->couponService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), CouponResource::make($result['data']));
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
     * @return JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->couponService->delete($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );

    }
}
