<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Throwable;
use App\Models\Settings;
use App\Traits\ApiResponse;
use App\Models\ParcelOrder;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Exports\ParcelOrderExport;
use App\Imports\ParcelOrderImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ParcelOrderResource;
use App\Http\Requests\Admin\ParcelOrder\StoreRequest;
use App\Http\Requests\Admin\ParcelOrder\UpdateRequest;
use App\Services\ParcelOrderService\ParcelOrderService;
use App\Http\Requests\Admin\ParcelOrder\StatusUpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Admin\ParcelOrder\DeliveryManUpdateRequest;
use App\Services\ParcelOrderService\ParcelOrderStatusUpdateService;
use App\Repositories\ParcelOrderRepository\AdminParcelOrderRepository;

class ParcelOrderController extends Controller
{
    use ApiResponse;
    public function __construct(
        private AdminParcelOrderRepository $repository,
        private ParcelOrderService $service
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $orders = $this->repository->ordersPaginate($request->all());

        return ParcelOrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $autoApprove = Settings::adminSettings()->where('key', 'parcel_order_auto_approved')->first();

        if ((int)$autoApprove?->value === 1) {
            $validated['status'] = ParcelOrder::STATUS_ACCEPTED;
        }

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderResource::make(data_get($result, 'data')),
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
        $parcelOrder = $this->repository->getById($id);

        if ($parcelOrder){
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
     * Update the specified resource in storage.
     *
     * @param ParcelOrder $parcelOrder
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(ParcelOrder $parcelOrder, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($parcelOrder, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderResource::make(data_get($result, 'data')),
        );
    }

    /**
     * Update Order DeliveryMan Update.
     *
     * @param int $orderId
     * @param DeliveryManUpdateRequest $request
     * @return JsonResponse
     */
    public function orderDeliverymanUpdate(int $orderId, DeliveryManUpdateRequest $request): JsonResponse
    {
        $result = $this->service->updateDeliveryMan($orderId, $request->input('deliveryman_id'));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderResource::make(data_get($result, 'data')),
        );
    }

    /**
     * Update Order Status.
     *
     * @param int $id
     * @param StatusUpdateRequest $request
     * @return JsonResponse
     */
    public function orderStatusUpdate(int $id, StatusUpdateRequest $request): JsonResponse
    {
        /** @var ParcelOrder $model */
        $model = ParcelOrder::with([
            'deliveryman',
            'user.wallet',
        ])->find($id);

        if (!$model) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_404,
                'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        if (!$model->user) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_502,
                'message'   => __('errors.' . ResponseError::ERROR_502, locale: $this->language)
            ]);
        }

        $result = (new ParcelOrderStatusUpdateService)->statusUpdate($model, $request->input('status'));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR),
            ParcelOrderResource::make(data_get($result, 'data')),
        );
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->destroy($request->input('ids'));
        if (count($result) > 0) {

            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_400,
                'message'   => __('errors.' . ResponseError::ERROR_400, [
                    'ids' => implode(', #', $result)
                ], locale: $this->language)
            ]);

        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language)
        );
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function fileExport(FilterParamsRequest $request): JsonResponse
    {
        $fileName = 'export/parcel-orders.xls';

        try {
            $filter = $request->merge(['language' => $this->language])->all();

            Excel::store(new ParcelOrderExport($filter), $fileName, 'public');

            return $this->successResponse('Successfully exported', [
                'path'      => 'public/export',
                'file_name' => $fileName
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse(statusCode: ResponseError::ERROR_508, message: $e->getMessage());
        }
    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function fileImport(FilterParamsRequest $request): JsonResponse
    {
        try {

            Excel::import(new ParcelOrderImport($this->language), $request->file('file'));

            return $this->successResponse('Successfully imported');
        } catch (Throwable $e) {
            return $this->errorResponse(statusCode: ResponseError::ERROR_508, message: $e->getMessage());
        }
    }
}
