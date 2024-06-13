<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\User;
use App\Models\Order;
use App\Traits\Notification;
use Illuminate\Http\Request;
use App\Models\OrderTemplate;
use App\Helpers\ResponseError;
use App\Models\PushNotification;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderResource;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderTemplateResource;
use App\Http\Requests\Review\AddReviewRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OrderService\OrderReviewService;
use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\OrderService\OrderStatusUpdateService;
use App\Http\Requests\User\Order\OrderTemplateStoreRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends UserBaseController
{
    use Notification;

    public function __construct(protected OrderRepoInterface $orderRepository, protected OrderServiceInterface $orderService)
    {
        parent::__construct();
    }


    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15,
            auth('sanctum')->id(), $request->all());
            return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->orderService->create($collection);
        if ($result['status']) {

            $tokens = $this->tokens($result);

            $this->sendNotification(
                data_get($tokens, 'tokens'),
                "New order was created",
                data_get($result, 'data.id'),
                data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_ORDER)?->only(['id', 'status', 'type']),
                data_get($tokens, 'ids', [])
            );

            
            return $this->successResponse(__('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function updateOrderTemplate(OrderTemplateStoreRequest $request, $id): JsonResponse
    {
        $orderTemplate = OrderTemplate::find($id);

        $collection = $request->validated();

        if ($orderTemplate) {

            $this->orderService->updateAutoOrder($orderTemplate, $collection);

            return $this->successResponse(ResponseError::NO_ERROR, OrderTemplateResource::make($orderTemplate));

        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
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
        $order = $this->orderRepository->show($id);
        if ($order && $order->user_id == auth('sanctum')->id()) {
            return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param AddReviewRequest $request
     * @return JsonResponse
     */
    public function addOrderReview(int $id, AddReviewRequest $request): JsonResponse
    {
        $result = (new OrderReviewService())->addReview($id, $request);
        if ($result['status']) {
            return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param AddReviewRequest $request
     * @return JsonResponse
     */
    public function addDeliverymanReview(int $id, AddReviewRequest $request): JsonResponse
    {
        $result = (new OrderReviewService)->addDeliverymanReview($id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            ResponseError::NO_ERROR,
            $this->orderRepository->reDataOrder(data_get($result, 'data'))
        );
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStatusChange(int $id, Request $request): JsonResponse
    {
//        if (!isset($request->status) || $request->status != 'canceled') {
//            return $this->errorResponse(ResponseError::ERROR_253, trans('errors.' . ResponseError::ERROR_253, [], \request()->lang ?? config('app.locale')),
//                Response::HTTP_BAD_REQUEST
//            );
//        }

        $order = Order::find($id);
        if ($order) {
            $result = (new OrderStatusUpdateService())->statusUpdate($order, $request->status);
            if ($result['status']) {
                return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function orderTemplate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $user = auth('sanctum')->user();
        $orderTemplates = OrderTemplate::with([
            'order',
            'order.orderDetails'
        ])->whereHas('order',function ($q) use ($user){
            $q->where('user_id',$user->id);
        })
            ->when(isset($collection['expired']), function ($q) {
            $q->where('date->end_date', '<', now());
        })->when(isset($collection['active']), function ($q) {
            $q->where('date->end_date', '>', now());
        })
            ->when(isset($collection['new']), function ($q) {
                $q->where('date->end_date', '>', now())->where('date->start_date', '<', now());
            })
            ->paginate($collection['perPage'] ?? 15);

        return OrderTemplateResource::collection($orderTemplates);
    }

    public function tokens($result): array
    {
        $adminFirebaseTokens = User::with([
            'roles' => fn($q) => $q->where('name', 'admin')
        ])
            ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->whereNotNull('firebase_token')
            ->pluck('firebase_token', 'id')
            ->toArray();

        $sellersFirebaseTokens = User::with([
            'shop' => fn($q) => $q->where('id', data_get($result, 'data.shop_id'))
        ])
            ->whereHas('shop', fn($q) => $q->where('id', data_get($result, 'data.shop_id')))
            ->whereNotNull('firebase_token')
            ->pluck('firebase_token', 'id')
            ->toArray();

        $aTokens = [];
        $sTokens = [];

        foreach ($adminFirebaseTokens as $adminToken) {
            $aTokens = array_merge($aTokens, is_array($adminToken) ? array_values($adminToken) : [$adminToken]);
        }

        foreach ($sellersFirebaseTokens as $sellerToken) {
            $sTokens = array_merge($sTokens, is_array($sellerToken) ? array_values($sellerToken) : [$sellerToken]);
        }

        return [
            'tokens' => array_values(array_unique(array_merge($aTokens, $sTokens))),
            'ids' => array_merge(array_keys($adminFirebaseTokens), array_keys($sellersFirebaseTokens))
        ];
    }
}
