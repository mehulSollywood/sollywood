<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Shop;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ShopWorkingDayResource;
use App\Http\Requests\Admin\ShopWorkingDay\StoreRequest;
use App\Services\ShopWorkingDayService\ShopWorkingDayService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ShopWorkingDayRepository\ShopWorkingDayRepository;

class ShopWorkingDayController extends AdminBaseController
{

    public function __construct(protected ShopWorkingDayRepository $repository,protected ShopWorkingDayService $service)
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
        $shopsWithWorkingDays = $this->repository->paginate($request->all());

        return ShopResource::collection($shopsWithWorkingDays);
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $shop = Shop::whereUuid($uuid)->first();

        if (empty($shop)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $shopWorkingDays = $this->repository->show($shop->id);

        return $this->successResponse(ResponseError::NO_ERROR, [
            'dates' => ShopWorkingDayResource::collection($shopWorkingDays),
            'shop'  => ShopResource::make($shop),
        ]);
    }

    /**
     * NOT USED
     * Display the specified resource.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->service->create($request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(ResponseError::NO_ERROR, []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param string $uuid
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(string $uuid, StoreRequest $request): JsonResponse
    {
        $shop = Shop::whereUuid($uuid)->first();

        if (empty($shop)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $result = $this->service->update($shop->id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $this->service->destroy($request->input('ids', []));

        return $this->successResponse(__('web.record_has_been_successfully_delete'), []);
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
