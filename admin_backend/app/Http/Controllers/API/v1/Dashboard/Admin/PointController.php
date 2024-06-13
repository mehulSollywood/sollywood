<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Point;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PointResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Services\PointService\PointService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Point\StoreRequest;
use App\Http\Requests\Admin\Point\UpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PointController extends AdminBaseController
{

    public function __construct(protected Point $model,protected PointService $pointService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $points = $this->model
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return PointResource::collection($points);
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
        $point = $this->model->create($collection);

        if ($point) {
            return $this->successResponse( __('web.record_was_successfully_create'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_400,  trans('errors.' . ResponseError::ERROR_400, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $point = $this->model->find($id);
        if ($point) {
            return $this->successResponse(__('web.product_found'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language ?? config('app.locale')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $collection = $request->validated();
        $point = $this->model->find($id);
        if ($point) {
            $point->update($collection);
            return $this->successResponse(__('web.record_was_successfully_update'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->pointService->delete($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
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
        $point = $this->model->find($id);
        if ($point) {
            $point->update(['active' => !$point->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
