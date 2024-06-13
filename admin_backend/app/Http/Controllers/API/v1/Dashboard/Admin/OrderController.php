<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Throwable;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Str;
use App\Exports\OrderExport;
use Illuminate\Http\Request;
use App\Traits\Notification;
use App\Helpers\ResponseError;
use App\Models\PushNotification;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\OrderResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Services\OrderService\OrderService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Order\ChartRequest;
use App\Http\Requests\Admin\Order\StoreRequest;
use App\Http\Requests\Admin\Order\UpdateRequest;
use App\Http\Requests\Order\ChartPaginateRequest;
use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepoInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Admin\Order\DebitOrderTransactionsRequest;
use App\Http\Requests\Admin\Order\DebitOrderTransactionStatusChange;

class OrderController extends AdminBaseController
{
    use Notification;

    public function __construct(protected OrderRepoInterface $orderRepository,protected OrderServiceInterface $orderService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $orders = $this->orderRepository->ordersList();

        return OrderResource::collection($orders);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function paginate(FilterParamsRequest $request): JsonResponse
    {
        $filter = $request->all();
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15, $request->user_id ?? null, $request->all());

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
//             Select Seller Firebase Token to Push Notification

            /** @var Shop $shop */
            $shop  = Shop::with(['seller'])->find(data_get($collection, 'shop_id'));

            $seller = $shop?->seller;
            $firebaseToken = $seller?->firebase_token;

            $this->sendNotification(
                is_array($firebaseToken) ? $firebaseToken : [],
                "New order was created",
                $result['data']->id,
                data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_ORDER)?->only(['id', 'status', 'type']),
                $seller?->id ? [$seller->id] : []
            );

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
        $order = $this->orderRepository->show($id);
        if ($order) {
            return $this->successResponse(__('web.language_found'), OrderResource::make($order));
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
        $result = $this->orderService->update($id, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function fileExport(FilterParamsRequest $request): JsonResponse
    {
        $time = Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

        $fileName = 'export/' . $time . '-orders.xlsx';

        try {
            $filter = $request->all();

            Excel::store(new OrderExport($filter), $fileName, 'public');

            return $this->successResponse('Successfully exported', [
                'path'      => 'public/export',
                'file_name' => $fileName
            ]);
        } catch (Throwable $e) {
            $this->error($e);
            return $this->errorResponse(statusCode: ResponseError::ERROR_508, message: $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DeleteAllRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->orderService->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function orderStatusUpdate(int $id, FilterParamsRequest $request): JsonResponse
    {
        $order = Order::find($id);
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

    public function orderDeliverymanUpdate(int $id, Request $request): JsonResponse
    {
        $result = $this->orderService->updateDeliveryMan($id, $request->input('deliveryman'));
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_updated'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }
    public function debitOrderTransactions(DebitOrderTransactionsRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $order = $this->orderRepository->debitOrderTransactions($validated);

        return OrderResource::collection($order);
    }

    public function debitOrderTransactionStatusChange(DebitOrderTransactionStatusChange $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->orderService->debitOrderTransactionStatusChange($id, $collection);

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

            $result = $this->orderRepository->ordersReportChart($collection);

            return $this->successResponse('Successfully found', $result);
        }

        public function reportChartPaginate(ChartPaginateRequest $request): JsonResponse
        {
            $collection = $request->validated();

            $result = $this->orderRepository->ordersReportChartPaginate($collection);

            return $this->successResponse(
                'Successfully data',
                $result
            );
        }

        public function revenueReport(ChartPaginateRequest $request): JsonResponse
        {
            $collection = $request->validated();
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
            try {
                $result = $this->orderRepository->overviewCategories($request->validated());

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
