<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BrandResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\ShopBrandResource;
use App\Http\Requests\ShopBrand\StoreRequest;
use App\Http\Requests\ShopBrand\UpdateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ShopBrandService\ShopBrandService;
use App\Repositories\BrandRepository\BrandRepository;
use App\Repositories\ShopBrandRepository\ShopBrandRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopBrandController extends SellerBaseController
{

    public function __construct(
        protected ShopBrandRepository $shopBrandRepository,
        protected ShopBrandService $shopBrandService,
        protected BrandRepository $brandRepository)
    {
        parent::__construct();
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $array['shop_id'] = $this->shop->id;
        $shopBrands = $this->shopBrandRepository->paginate($request->perPage ?? 15,$array);
        return ShopBrandResource::collection($shopBrands);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $array['shop_id'] = $this->shop->id;
        $shopBrands = $this->shopBrandRepository->paginate($request->perPage ?? 15,$array);
        return ShopBrandResource::collection($shopBrands);
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
        $result = $this->shopBrandService->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), $result['data']);
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

        $shopBrand = $this->brandRepository->shopBrandById($id, $this->shop->id);
        if ($shopBrand) {
            return $this->successResponse(__('web.coupon_found'), BrandResource::make($shopBrand));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $collection['shop_id'] = $this->shop->id;
        $result = $this->shopBrandService->update($collection, $this->shop);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), $result['data']);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->shopBrandService->destroy($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function allBrand(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $brand = $this->brandRepository->shopBrandNonExistPaginate($this->shop->id, $request->all(), $request->perPage ?? 10);
        if ($brand) {
            return BrandResource::collection($brand);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
