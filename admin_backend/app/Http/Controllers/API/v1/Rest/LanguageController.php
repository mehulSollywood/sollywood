<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LanguageController extends RestBaseController
{
    public function __construct(protected Language $model)
    {
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $languages = $this->model->orderByDesc('default')->get();
        return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), LanguageResource::collection($languages));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $language = $this->model->find($id);
        if ($language) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), LanguageResource::make($language));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Get Language where "default = 1".
     *
     * @return JsonResponse
     */
    public function default(): JsonResponse
    {
        $language = $this->model->whereDefault(1)->first();
        if ($language) {
            return $this->successResponse(__('web.language_found'), LanguageResource::make($language));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Get all Active languages
     * @return JsonResponse
     */
    public function active(): JsonResponse
    {
        $languages = $this->model->whereActive(1)->orderByDesc('default')->get();
        return $this->successResponse(__('web.list_of_active_languages'), LanguageResource::collection($languages));
    }
}
