<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Traits\Notification;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderDetailResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\OrderRepository\OrderDetailRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderDetailController extends AdminBaseController
{
    use Notification;

    public function __construct(protected OrderDetailRepository $detailRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $orderDetails = $this->detailRepository->paginate($request->perPage, $request->user_id, $request->all());
        return OrderDetailResource::collection($orderDetails);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $orderDetail = $this->detailRepository->orderDetailById($id);
        if ($orderDetail) {
            return $this->successResponse(__('web.language_found'), OrderDetailResource::make($orderDetail));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Update Order Delivery details by OrderDetail ID.
     *
     * @param int $orderDetail
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetailDeliverymanUpdate(int $orderDetail, Request $request): JsonResponse
    {
        $orderDetail = $this->detailRepository->orderDetailById($orderDetail);
        if ($orderDetail){
            $orderDetail->update([
                'deliveryman' => $request->deliveryman ?? $orderDetail->deliveryman,
            ]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), OrderDetailResource::make($orderDetail));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Calculate products when cart updated.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateOrderProducts(Request $request): JsonResponse
    {
        $result = $this->detailRepository->orderProductsCalculate($request->all());
        return $this->successResponse(__('web.products_calculated'), $result);
    }
}
