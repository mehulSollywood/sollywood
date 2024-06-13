<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RecipeResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\Recipe\StoreRequest;
use App\Http\Requests\Recipe\UpdateRequest;
use App\Services\RecipeService\RecipeService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\RecipeRepository\RecipeRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection as AnonymousResourceCollectionAlias;

class RecipeController extends SellerBaseController
{

    public function __construct(protected RecipeRepository $recipeRepository,protected RecipeService $recipeService)
    {
        parent::__construct();
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollectionAlias
    {
        $recipe = $this->recipeRepository->paginate($request->perPage, $request->all(), $this->shop->id);
        return RecipeResource::collection($recipe);
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
        $result = $this->recipeService->create($collection);
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

        $recipe = $this->recipeRepository->getById($id, $this->shop->id);
        if ($recipe) {
            return $this->successResponse(__('web.coupon_found'), RecipeResource::make($recipe));
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
        $collection['shop_id'] = $this->shop->id;
        $result = $this->recipeService->update($id, $collection);
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
    public function destroy(DeleteAllRequest $request): JsonResponse|AnonymousResourceCollectionAlias
    {
        $collection = $request->validated();
        $result = $this->recipeService->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function statusChange(string $id): JsonResponse|AnonymousResourceCollectionAlias
    {
        $recipe = $this->recipeRepository->getById($id);
        if ($recipe) {
            $recipe->update(['status' => !$recipe->status]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), RecipeResource::make($recipe));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
