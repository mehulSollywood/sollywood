<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ShopCategoryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ShopCategory\StoreRequest;
use App\Http\Requests\ShopCategory\UpdateRequest;
use App\Services\ShopCategoryService\ShopCategoryService;
use App\Repositories\CategoryRepository\CategoryRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ShopCategoryRepository\ShopCategoryRepository;

class ShopCategoryController extends SellerBaseController
{

    public function __construct(
        protected ShopCategoryRepository $shopCategoryRepository,
        protected ShopCategoryService    $shopCategoryService,
        protected CategoryRepository     $categoryRepository
    )
    {
        parent::__construct();
    }


    public function index(Request $request): AnonymousResourceCollection
    {
        $shopCategories = $this->shopCategoryRepository->paginate($request->perPage, $this->shop->id);
        return ShopCategoryResource::collection($shopCategories);
    }

    public function children(Request $request): AnonymousResourceCollection
    {
        $shopCategories = $this->shopCategoryRepository->children($request->perPage,$request->parent_id);
        return CategoryResource::collection($shopCategories);
    }

    public function childrenCategory(Request $request): AnonymousResourceCollection
    {
        $shopCategories = $this->categoryRepository->categoriesSearch(search: $request->search ?? '', shop_id: $this->shop->id);
        return CategoryResource::collection($shopCategories);
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $shopCategories = $this->shopCategoryRepository->shopCategoryPaginate($request->perPage);
        return ShopCategoryResource::collection($shopCategories);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function selectPaginate(Request $request): AnonymousResourceCollection
    {
        $shopBrands = $this->categoryRepository->selectPaginate($request->perPage);

        return CategoryResource::collection($shopBrands);
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
        $result = $this->shopCategoryService->create($collection);
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
        $shopBrand = $this->categoryRepository->shopCategoryById($id, $this->shop->id);
        if ($shopBrand) {
            return $this->successResponse(__('web.coupon_found'), CategoryResource::make($shopBrand));
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
        $result = $this->shopCategoryService->update($collection, $this->shop);
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
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();
        $result = $this->shopCategoryService->destroy($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function allCategory(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $category = $this->categoryRepository->shopCategoryNonExistPaginate($this->shop->id, $request->all(), $request->perPage ?? 10);
        if ($category) {
            return CategoryResource::collection($category);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
