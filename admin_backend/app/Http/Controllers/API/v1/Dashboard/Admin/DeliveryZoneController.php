<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\DeliveryZone;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\DeliveryZoneResource;
use App\Http\Requests\Admin\DeliveryZone\StoreRequest;
use App\Services\DeliveryZoneService\DeliveryZoneService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\DeliveryZoneRepository\DeliveryZoneRepository;

class DeliveryZoneController extends AdminBaseController
{

    public function __construct(protected DeliveryZoneService $service,protected DeliveryZoneRepository $repository)
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
        $deliveryZone = $this->repository->paginate($request->all());

        return DeliveryZoneResource::collection($deliveryZone);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $exist = DeliveryZone::where('shop_id', data_get($data, 'shop_id'))->first();

        if (!empty($exist)) {
            return $this->update($exist, $request);
        }

        $result = $this->service->create($request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_successfully_created'), []);
    }

    /**
     * Display the specified resource.
     *
     * @param DeliveryZone $deliveryZone
     * @return JsonResponse
     */
    public function show(DeliveryZone $deliveryZone): JsonResponse
    {
        return $this->successResponse(
            __('web.coupon_found'),
            DeliveryZoneResource::make($this->repository->show($deliveryZone))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DeliveryZone $deliveryZone
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(DeliveryZone $deliveryZone, StoreRequest $request): JsonResponse
    {
        $result = $this->service->update($deliveryZone, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_successfully_updated'), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->delete($request->input('ids', []));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_has_been_successfully_delete'));
    }

//    /**
//     * @return JsonResponse
//     */
//    public function dropAll(): JsonResponse
//    {
//        $this->service->dropAll();
//
//        return $this->successResponse( __('web.record_was_successfully_updated'), []);
//    }
//
//    /**
//     * @return JsonResponse
//     */
//    public function truncate(): JsonResponse
//    {
//        $this->service->truncate();
//
//        return $this->successResponse( __('web.record_was_successfully_updated'), []);
//    }
//
//    /**
//     * @return JsonResponse
//     */
//    public function restoreAll(): JsonResponse
//    {
//        $this->service->restoreAll();
//
//        return $this->successResponse( __('web.record_was_successfully_updated'), []);
//    }

}
