<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Exception;
use App\Models\DeliveryZone;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\DeliveryZoneResource;
use App\Http\Requests\Seller\DeliveryZone\StoreRequest;
use App\Http\Requests\Seller\DeliveryZone\UpdateRequest;
use App\Services\DeliveryZoneService\DeliveryZoneService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\DeliveryZoneRepository\DeliveryZoneRepository;

class DeliveryZoneController extends SellerBaseController
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
        $deliveryZone = $this->repository->paginate($request->merge(['shop_id' => $this->shop->id])->all());

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
        $data['shop_id'] = $this->shop->id;

        $result = $this->service->create($data);

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
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function update(DeliveryZone $deliveryZone, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($deliveryZone, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
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

        $this->service->delete($request->input('ids', []), $this->shop->id);

        return $this->successResponse(__('web.record_has_been_successfully_delete'));
    }

}
