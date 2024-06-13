<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\RecipeCategoryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\RecipeCategory\StoreRequest;
use App\Http\Requests\RecipeCategory\UpdateRequest;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use App\Services\RecipeCategoryService\RecipeCategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\RecipeCategoryRepository\RecipeCategoryRepository;

class RecipeCategoryController extends AdminBaseController
{
    use ApiResponse;

    public function __construct(protected RecipeCategoryService $recipeCategoryService,protected RecipeCategoryRepository $recipeCategoryRepository)
    {
        parent::__construct();
    }

    public function store(StoreRequest $request): JsonResponseAlias
    {
        $collection = $request->validated();
            $result = $this->recipeCategoryService->create($collection);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_created'), $result['data']);
            }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );

    }

    public function update(UpdateRequest $request,int $id): JsonResponseAlias
    {
        $collection = $request->validated();
        $result = $this->recipeCategoryService->update($collection,$id);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_updated'), $result['data']);
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
    }

    public function show(int $id): JsonResponseAlias
    {
        $recipeCategory = $this->recipeCategoryRepository->getById($id);
        if ($recipeCategory){
            return $this->successResponse(__('web.coupon_found'), RecipeCategoryResource::make($recipeCategory));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $recipeCategory = $this->recipeCategoryRepository->paginate($request->perPage, $request->all());
        return RecipeCategoryResource::collection($recipeCategory);
    }

    public function statusChange(string $id): JsonResponseAlias|AnonymousResourceCollection
    {
        $recipeCategory = $this->recipeCategoryRepository->getById($id);
        if ($recipeCategory) {
            $recipeCategory->update(['status' => !$recipeCategory->status]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), RecipeCategoryResource::make($recipeCategory));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponseAlias|AnonymousResourceCollection
    {
        $collection = $request->validated();

        $result = $this->recipeCategoryService->delete($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }
}
