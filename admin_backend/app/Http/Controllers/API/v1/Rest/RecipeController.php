<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\RecipeResource;
use App\Repositories\RecipeRepository\RecipeRepository;
use App\Services\RecipeService\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class RecipeController extends RestBaseController
{
    public function __construct(protected RecipeRepository $recipeRepository,protected RecipeService $recipeService)
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
        $recipes = $this->recipeRepository->paginate($request->perPage,$request->all(),$request->shop_id, true);
        return RecipeResource::collection($recipes);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $recipe = $this->recipeRepository->getById($id);
        if ($recipe){
            return $this->successResponse(__('web.recipe_found'), RecipeResource::make($recipe));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
