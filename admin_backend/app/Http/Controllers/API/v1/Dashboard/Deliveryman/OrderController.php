<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderResource;
use App\Http\Requests\FilterParamsRequest;
use App\Services\OrderService\OrderService;
use App\Http\Requests\Review\AddReviewRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\OrderService\OrderReviewService;
use App\Repositories\OrderRepository\OrderRepository;
use App\Http\Requests\DeliveryMan\Order\ReportRequest;
use App\Services\OrderService\OrderStatusUpdateService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\DeliveryMan\Order\DebitOrderTransactionsRequest;
use App\Http\Requests\DeliveryMan\Order\DebitOrderTransactionStatusChange;

class OrderController extends DeliverymanBaseController
{

    public function __construct(protected Order $model, protected OrderService $service, protected OrderRepository $repository)
    {
        parent::__construct();
    }

    public function paginate(Request $request): JsonResponse|AnonymousResourceCollection
    {
        /** @var User $user */

        $filter = $request->all();

        $filter['deliveryman'] = auth('sanctum')->id();

        $user = auth('sanctum')->user();

        unset($filter['isset-deliveryman']);

        if (data_get($filter, 'empty-deliveryman')) {
            $filter['shop_ids'] = $user->invitations->pluck('shop_id')->toArray();
            unset($filter['deliveryman']);
        }

        $orders = $this->model
            ->withCount('orderDetails')
            ->with([
                'deliveryMan',
                'currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                },
                'transaction.paymentSystem.payment.translation',
                'shop.translation',
                'user'
            ])
            ->filter($filter)
            ->orderBy('id', 'desc')
            ->paginate($request->perPage ?? 15);

        $statistic = (new OrderRepository())->orderByStatusStatistics($filter);

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), [
            'statistic' => $statistic,
            'orders' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => (int)data_get($filter, 'page', 1),
                'per_page' => (int)data_get($filter, 'perPage', 10),
                'last_page' => ceil($orders->total() / (int)data_get($filter, 'perPage', 10)),
                'total' => $orders->total()
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->model
            ->with([
                'user',
                'review',
                'deliveryType.translation',
                'deliveryAddress',
                'deliveryMan',
                'coupon',
                'shop.translation',
                'transaction.paymentSystem.payment' => function ($q) {
                    $q->select('id', 'tag', 'active');
                },
                'transaction.paymentSystem.payment.translation:id,locale,payment_id,title',
                'orderDetails.shopProduct.product.translation:id,product_id,locale,title',
                'currency:id,title,symbol'
            ])
            ->find($id);

        if ($order) {
            return $this->successResponse(__('web.order_found'), OrderResource::make($order));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param int $id
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function orderStatusUpdate(int $id, FilterParamsRequest $request): JsonResponse
    {
        /** @var Order $order */
        /** @var User $user */

        $statuses = [
            Order::READY => Order::READY,
            Order::ON_A_WAY => Order::ON_A_WAY,
            Order::DELIVERED => Order::DELIVERED
        ];

        if (!data_get($statuses, $request->input('status'))) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_253]);
        }

        $order = Order::with([
            'shop.seller',
            'deliveryMan',
            'user',
        ])->find($id);


        if (!$order) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }
        $user = auth('sanctum')->user();
        $order->update(['deliveryman' => $user->id]);

        $result = (new OrderStatusUpdateService)->statusUpdate($order, $request->input('status'), true);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR),
            OrderResource::make(data_get($result, 'data'))
        );

    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param AddReviewRequest $request
     * @return JsonResponse
     */
    public function addReviewByDeliveryman(int $id, AddReviewRequest $request): JsonResponse
    {
        $result = (new OrderReviewService)->addReviewByDeliveryman($id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            ResponseError::NO_ERROR,
            OrderResource::make(data_get($result, 'data'))
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
            OrderResource::make(data_get($result, 'data'))
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
            __('web.delivery_man_setting_found'),
            OrderResource::make(data_get($result, 'data'))
        );
    }

    public function report(ReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['deliveryman'] = auth('sanctum')->id();

        return $this->successResponse(
            __('web.report_found'),
            $this->repository->deliveryManReport($validated)
        );
    }

    public function debitOrderTransactions(DebitOrderTransactionsRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $validated['deliveryman_id'] = auth('sanctum')->id();

        $orders = $this->repository->debitOrderTransactions($validated);

        return OrderResource::collection($orders);
    }

    public function debitOrderTransactionStatusChange(DebitOrderTransactionStatusChange $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->service->debitOrderTransactionStatusChange($id, $collection);

        if ($result['status']){
            return $this->successResponse(
                __('web.status_changes'),
                $result['data']
            );
        }

        return $this->onErrorResponse($result);
    }
}
