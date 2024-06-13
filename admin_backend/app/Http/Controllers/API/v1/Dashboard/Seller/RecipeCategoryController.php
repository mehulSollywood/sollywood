<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RecipeCategoryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\RecipeCategoryService\RecipeCategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\RecipeCategoryRepository\RecipeCategoryRepository;

class RecipeCategoryController extends SellerBaseController
{

    public function __construct(
        protected RecipeCategoryService $recipeCategoryService,
        protected RecipeCategoryRepository $recipeCategoryRepository
    )
    {
        parent::__construct();
    }

    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $recipeCategory = $this->recipeCategoryRepository->getById($id, true);
        if ($recipeCategory){
            $recipeCategory->load('recipe.recipeProduct','recipe.user');
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
        $recipeCategory = $this->recipeCategoryRepository->paginate($request->perPage, true);
        return RecipeCategoryResource::collection($recipeCategory);
    }
}
