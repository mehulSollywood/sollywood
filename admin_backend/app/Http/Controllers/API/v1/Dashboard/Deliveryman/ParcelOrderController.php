<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Models\ParcelOrder;
use App\Traits\Notification;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ParcelOrderResource;
use App\Services\ParcelOrderService\ParcelOrderService;
use App\Http\Requests\User\ParcelOrder\StatusUpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ParcelOrderRepository\ParcelOrderRepository;
use App\Services\ParcelOrderService\ParcelOrderStatusUpdateService;

class ParcelOrderController extends DeliverymanBaseController
{
    use Notification;

    public function __construct(private ParcelOrderRepository $repository, private ParcelOrderService $service) {
        parent::__construct();
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $request->all();

        $filter['deliveryman_id'] = auth('sanctum')->id();

        $orders = $this->repository->paginate($filter);

        return ParcelOrderResource::collection($orders);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var ParcelOrder $parcelOrder */
        $parcelOrder = $this->repository->show($id);

        if (!empty(data_get($parcelOrder, 'id'))) {
            return $this->successResponse(
                __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
                ParcelOrderResource::make($parcelOrder)
            );
        }

        return $this->onErrorResponse([
            'code'      => ResponseError::ERROR_404,
            'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
        ]);
    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param int $id
     * @param StatusUpdateRequest $request
     * @return JsonResponse
     */
    public function orderStatusUpdate(int $id, StatusUpdateRequest $request): JsonResponse
    {
        $statuses = [
            ParcelOrder::STATUS_READY     => ParcelOrder::STATUS_READY,
            ParcelOrder::STATUS_ON_A_WAY  => ParcelOrder::STATUS_ON_A_WAY,
            ParcelOrder::STATUS_DELIVERED => ParcelOrder::STATUS_DELIVERED
        ];

        if (!data_get($statuses, $request->input('status'))) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_253]);
        }

        /** @var ParcelOrder $parcelOrder */
        $parcelOrder = ParcelOrder::with([
            'deliveryman',
            'user.wallet',
        ])->find($id);

        if (!$parcelOrder) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $result = (new ParcelOrderStatusUpdateService)->statusUpdate($parcelOrder, $request->input('status'), true);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR),
            ParcelOrderResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int|null $id
     * @return JsonResponse
     */
    public function orderDeliverymanUpdate(?int $id): JsonResponse
    {
        $result = $this->service->attachDeliveryMan($id);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function setCurrent(int $id): JsonResponse
    {
        $result = $this->service->setCurrent($id, auth('sanctum')->id());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            ResponseError::NO_ERROR,
            ParcelOrderResource::make(data_get($result, 'data'))
        );
    }
}
