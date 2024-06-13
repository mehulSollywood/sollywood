<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Shop;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopResource;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ShopClosedDateResource;
use App\Http\Requests\Admin\ShopClosedDate\StoreRequest;
use App\Http\Requests\Admin\ShopClosedDate\UpdateRequest;
use App\Services\ShopClosedDateService\ShopClosedDateService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ShopClosedDateRepository\ShopClosedDateRepository;

class ShopClosedDateController extends AdminBaseController
{

    public function __construct(protected ShopClosedDateRepository $repository,protected ShopClosedDateService $service)
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
        Artisan::call('remove:expired_closed_dates');

        $shopsWithClosedDays = $this->repository->paginate($request->all());

        return ShopResource::collection($shopsWithClosedDays);
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
        $validated = $request->validated();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(ResponseError::NO_ERROR, []);
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

        $shopClosedDate = $this->repository->show($shop->id);

        return $this->successResponse(ResponseError::NO_ERROR, [
            'closed_dates'  => ShopClosedDateResource::collection($shopClosedDate),
            'shop'          => ShopResource::make($shop),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param string $uuid
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(string $uuid, UpdateRequest $request): JsonResponse
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
