<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\BonusProductResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\BonusService\BonusProductService;
use App\Http\Requests\Seller\BonusProduct\StoreRequest;
use App\Http\Requests\Seller\BonusProduct\UpdateRequest;
use App\Repositories\BonusRepository\BonusProductRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BonusProductController extends SellerBaseController
{

    public function __construct(protected BonusProductRepository $repository,protected BonusProductService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $bonusShops = $this->repository->paginate($request->perPage ?? 15,$this->shop->id);
        return BonusProductResource::collection($bonusShops);
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
        $result = $this->service->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BonusProductResource::make($result['data']));
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
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $shopBonus = $this->repository->show($id);
        if ($shopBonus) {
            return $this->successResponse(__('web.coupon_found'), BonusProductResource::make($shopBonus));
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
            return $this->successResponse(__('web.record_successfully_updated'), BonusProductResource::make($result['data']));
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
