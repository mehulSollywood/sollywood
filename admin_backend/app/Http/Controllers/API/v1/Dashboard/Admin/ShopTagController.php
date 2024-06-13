<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\ShopTag;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopTagResource;
use App\Http\Requests\FilterParamsRequest;
use App\Services\ShopTagService\ShopTagService;
use App\Http\Requests\Admin\ShopTag\StoreRequest;
use App\Repositories\ShopTagRepository\ShopTagRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopTagController extends AdminBaseController
{

    public function __construct(protected ShopTagService $service,protected ShopTagRepository $repository)
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
        $shopTag = $this->repository->paginate($request->all());

        return ShopTagResource::collection($shopTag);
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

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('web.record_successfully_created'),
            ShopTagResource::make(data_get($result, 'data'))
        );
    }

    /**
     * @param ShopTag $shopTag
     * @return JsonResponse
     */
    public function show(ShopTag $shopTag): JsonResponse
    {
        return $this->successResponse(
            __('web.coupon_found'),
            ShopTagResource::make($this->repository->show($shopTag))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ShopTag $shopTag
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(ShopTag $shopTag, StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->update($shopTag, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('web.record_has_been_successfully_updated'),
            ShopTagResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->delete($request->input('ids', []));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(__('web.record_has_been_successfully_delete'));
    }
}
