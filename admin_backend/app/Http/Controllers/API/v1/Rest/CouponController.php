<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Coupon;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CouponResource;
use App\Http\Requests\CouponCheckRequest;
use Symfony\Component\HttpFoundation\Response;

class CouponController extends RestBaseController
{
    public function __construct(protected Coupon $model)
    {
        parent::__construct();
    }

    /**
     * Handle the incoming request.
     *
     * @param CouponCheckRequest $request
     * @return JsonResponse
     */
    public function __invoke(CouponCheckRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $shopCoupon = $this->model->where('expired_at','>',now())->firstWhere(['shop_id' => $collection['shop_id'], 'name' => $collection['coupon']]);

        if (!$shopCoupon) {
            return $this->errorResponse(ResponseError::ERROR_249, trans('errors.' . ResponseError::ERROR_249, [], $this->language), Response::HTTP_NOT_FOUND);
        }

        $coupon = $this->model->checkCoupon($collection['coupon'])->first();

        if (!$coupon) {
            return $this->errorResponse(ResponseError::ERROR_250, trans('errors.' . ResponseError::ERROR_250, [], $this->language),
                Response::HTTP_NOT_FOUND);
        }

        $orderCoupon = $coupon->orderCoupon()->firstWhere('user_id', $collection['user_id']);

        if (!$orderCoupon) {
            return $this->successResponse(__('web.coupon_found'), CouponResource::make($coupon));
        }
        return $this->errorResponse(ResponseError::ERROR_251, trans('errors.' . ResponseError::ERROR_251, [], $this->language),
            Response::HTTP_NOT_FOUND);

    }
}
