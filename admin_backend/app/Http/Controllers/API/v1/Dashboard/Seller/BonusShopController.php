<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\BonusShopResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\BonusService\BonusShopService;
use App\Http\Requests\Seller\BonusShop\StoreRequest;
use App\Http\Requests\Seller\BonusShop\UpdateRequest;
use App\Repositories\BonusRepository\BonusShopRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BonusShopController extends SellerBaseController
{

    public function __construct(protected BonusShopService $service,protected BonusShopRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $bonusShops = $this->repository->paginate($request->perPage ?? 15,$this->shop);
        return BonusShopResource::collection($bonusShops);
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
        $result = $this->service->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BonusShopResource::make($result['data']));
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
        $shopBonus = $this->repository->show($id);
        if ($shopBonus) {
            return $this->successResponse(__('web.coupon_found'), BonusShopResource::make($shopBonus));
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
        $result = $this->service->update($id, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), BonusShopResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {

        $collection = $request->validated();
        $result = $this->service->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function statusChange(int $id): JsonResponse
    {
        $result = $this->service->statusChange($id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_change'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }


}
