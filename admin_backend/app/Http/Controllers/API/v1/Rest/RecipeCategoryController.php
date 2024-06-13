<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\RecipeCategoryResource;
use App\Repositories\RecipeCategoryRepository\RecipeCategoryRepository;
use App\Services\RecipeCategoryService\RecipeCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;


class RecipeCategoryController extends RestBaseController
{
    public function __construct(
        protected RecipeCategoryService $recipeCategoryService,
        protected RecipeCategoryRepository $recipeCategoryRepository
    )
    {
        parent::__construct();
    }

    public function show(int $id): JsonResponse
    {
        $recipeCategory = $this->recipeCategoryRepository->getById($id, true);
        if ($recipeCategory){
            return $this->successResponse(__('web.coupon_found'), RecipeCategoryResource::make($recipeCategory));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $recipeCategory = $this->recipeCategoryRepository->paginateForRest($request->perPage ?? 15,$request->all());
        return RecipeCategoryResource::collection($recipeCategory);
    }

}
