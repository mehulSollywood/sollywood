<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RecipeCategoryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\RecipeCategoryService\RecipeCategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\RecipeCategoryRepository\RecipeCategoryRepository;

class RecipeCategoryController extends UserBaseController
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
        $recipeCategory = $this->recipeCategoryRepository->getById($id);
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
        $recipeCategory = $this->recipeCategoryRepository->paginate($request->perPage,$request->all());
        return RecipeCategoryResource::collection($recipeCategory);
    }
}
