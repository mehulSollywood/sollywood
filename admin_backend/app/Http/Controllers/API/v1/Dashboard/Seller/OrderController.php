<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Throwable;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Traits\Notification;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\OrderResource;
use App\Http\Requests\FilterParamsRequest;
use App\Services\OrderService\OrderService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Order\ChartRequest;
use App\Http\Requests\Seller\Order\StoreRequest;
use App\Http\Requests\Order\ChartPaginateRequest;
use App\Http\Requests\Seller\Order\UpdateRequest;
use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepoInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Seller\Order\DebitOrderTransactionsRequest;
use App\Http\Requests\Seller\Order\DebitOrderTransactionStatusChange;

class OrderController extends SellerBaseController
{
    use Notification;

    public function __construct(protected OrderRepoInterface $orderRepository,protected OrderServiceInterface $orderService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function paginate(FilterParamsRequest $request): JsonResponse
    {
        $filter = $request->merge(['shop_id' => $this->shop->id])->all();
        $orders = $this->orderRepository->ordersPaginate(
            $request->perPage ?? 15, $request->user_id ?? null,
            $filter);

        $statistic  = $this->orderRepository->orderByStatusStatistics($filter);

        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), [
            'statistic' => $statistic,
            'orders'    =>  OrderResource::collection($orders),
            'meta'      => [
                'current_page'  => (int)data_get($filter, 'page', 1),
                'per_page'      => (int)data_get($filter, 'perPage', 10),
                'last_page'     => ceil($orders->total()/(int)data_get($filter, 'perPage', 10)),
                'total'         => $orders->total()
            ],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return array
     */
    public function orderChartPaginate(FilterParamsRequest $request): array
    {
        $filter = $request->merge(['shop_id' => $this->shop->id])->all();

        return $this->orderRepository->orderChartPaginate($filter);
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
        $collection['shop_id'] = $this->shop->id;
        $result = $this->orderService->create($collection);

        if ($result['status']) {

            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('role_id', [99, 21]);
            })->pluck('firebase_token');
            Log::info("SELLER NOTIFICATION", $admins->toArray());

            $this->sendNotification($admins->toArray(), "New order was created", $result['data']->id);

            return $this->successResponse(__('web.record_was_successfully_create'), OrderResource::make($result['data']));
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
        $order = $this->orderRepository->show($id, $this->shop->id);
        if ($order) {
            return $this->successResponse(__('web.order_found'), OrderResource::make($order));
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
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $collection['shop_id'] = $this->shop->id;
        $result = $this->orderService->update($id, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param int $order
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function orderStatusUpdate(int $order, FilterParamsRequest $request): JsonResponse
    {
        $order = Order::where('shop_id', $this->shop->id)->find($order);
        if ($order) {
            $result = (new OrderService())->updateStatus($order, $request->status ?? null);
            if ($result['status']) {
                return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), OrderResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Update Order Delivery details by OrderDetail ID.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetailDeliverymanUpdate(int $id, Request $request): JsonResponse
    {
        $result = $this->orderService->updateDeliveryMan($id, $request->input('deliveryman'),$this->shop->id);
        if ($result['status']) {
            return $this->successResponse(__('web.products_calculated'),$result['data']);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function debitOrderTransactions(DebitOrderTransactionsRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $validated['shop_id'] = $this->shop->id;

        $orders = $this->orderRepository->debitOrderTransactions($validated);

        return OrderResource::collection($orders);
    }

    public function debitOrderTransactionStatusChange(DebitOrderTransactionStatusChange $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $result = $this->orderService->debitOrderTransactionStatusChangeSeller($id, $collection);

        if ($result['status']){
            return $this->successResponse(
                __('web.status_changes'),
                $result['data']
            );
        }

        return $this->onErrorResponse($result);
    }

    /**
     * Calculate products when cart updated.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateOrderProducts(Request $request): JsonResponse
    {
        $result = $this->orderService->productsCalculate($request->all());
        return $this->successResponse(__('web.products_calculated'), $result);
    }

    /**
     * @param ChartRequest $request
     * @return JsonResponse
     */
    public function reportChart(ChartRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $result = $this->orderRepository->ordersReportChart($collection);

        return $this->successResponse('Successfully found', $result);
    }

    public function reportChartPaginate(ChartPaginateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $result = $this->orderRepository->ordersReportChartPaginate($collection);

        return $this->successResponse(
            'Successfully data',
            $result
        );
    }

    public function revenueReport(ChartPaginateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        try {
            $result = $this->orderRepository->revenueReport($collection);

            return $this->successResponse(
                'Successfully data',
                $result
            );
        } catch (Throwable $e) {

            $this->error($e);

            return $this->errorResponse(
                ResponseError::ERROR_400, trans('errors.' . ResponseError::ERROR_400, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function overviewCarts(ChartPaginateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        try {
            $result = $this->orderRepository->overviewCarts($collection);

            return $this->successResponse(
                'Successfully data',
                $result
            );
        } catch (Throwable $e) {
            $this->error($e);

            return $this->errorResponse(
                ResponseError::ERROR_400, trans('errors.' . ResponseError::ERROR_400, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function overviewProducts(ChartPaginateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        try {
            $result = $this->orderRepository->overviewProducts($collection);

            return $this->successResponse(
                'Successfully data',
                $result
            );
        } catch (Throwable $e) {
            $this->error($e);
            return $this->errorResponse(
                ResponseError::ERROR_400, trans('errors.' . ResponseError::ERROR_400, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function overviewCategories(ChartPaginateRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        try {
            $result = $this->orderRepository->overviewCategories($collection);

            return $this->successResponse(
                'Successfully data',
                $result
            );
        } catch (Throwable $e) {
            $this->error($e);

            return $this->errorResponse(
                ResponseError::ERROR_400, trans('errors.' . ResponseError::ERROR_400, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }




}
