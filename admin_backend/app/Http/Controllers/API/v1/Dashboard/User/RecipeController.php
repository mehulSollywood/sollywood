<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RecipeResource;
use App\Services\RecipeService\RecipeService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\RecipeRepository\RecipeRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecipeController extends UserBaseController
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
        $recipes = $this->recipeRepository->paginate($request->perPage);
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
