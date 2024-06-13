<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\LanguageResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Interfaces\LanguageServiceInterface;

class LanguageController extends AdminBaseController
{

    public function __construct(
        protected LanguageServiceInterface $languageService,
        protected Language $model
    )
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
        $languages = $this->model->languagesList();
        return $this->successResponse(__('web.list_of_languages'), LanguageResource::collection($languages));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->languageService->create($request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), LanguageResource::make($result['data']));
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
        $language = $this->model->find($id);
        if ($language) {
            return $this->successResponse(__('web.language_found'), LanguageResource::make($language));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->languageService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), LanguageResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->languageService->destroy($id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Get Language where "default = 1".
     *
     * @return JsonResponse
     */
    public function getDefaultLanguage(): JsonResponse
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
     * Make specific Language as default
     * @param int $id
     * @return JsonResponse
     */
    public function setDefaultLanguage(int $id): JsonResponse
    {
        $result = $this->languageService->setLanguageDefault($id, 1);
        if ($result['status']) {
            return $this->successResponse(__('web.item_is_default_now'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Get all Active languages
     * @return JsonResponse
     */
    public function getActiveLanguages(): JsonResponse
    {
        $languages = $this->model->whereActive(1)->get();
        return $this->successResponse(__('web.list_of_active_languages'), LanguageResource::collection($languages));
    }

    /**
     * Remove Model image from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function imageDelete(int $id): JsonResponse
    {
        $language = $this->model->find($id);
        if ($language) {
            Storage::disk('public')->delete($language->img);
            $language->update(['img' => null]);

            return $this->successResponse(__('web.image_has_been_successfully_delete'), $language);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function setActive(int $id): JsonResponse
    {
        $lang = $this->model->find($id);
        if ($lang) {
            $lang->update(['active' => !$lang->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), LanguageResource::make($lang));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
