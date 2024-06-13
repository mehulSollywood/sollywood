<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Illuminate\Http\Request;
use App\Models\TermCondition;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Services\TermService\TermService;
use Symfony\Component\HttpFoundation\Response;

class TermsController extends AdminBaseController
{

    public function __construct(protected TermCondition $model,protected TermService $service)
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->model::query()->delete();
        $condition = $this->service->create($request);
        if ($condition['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), $condition['data']);
        }
        return $this->errorResponse(
            ResponseError::ERROR_501, trans('errors.' . ResponseError::ERROR_501, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $model = $this->model->with([
            'translation',
            'translations'
        ])->first();

        if ($model){
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $term = $this->service->update($id,$request);
        if ($term['status']){
            return $this->successResponse(__('web.record_has_been_successfully_updated'), $term);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
