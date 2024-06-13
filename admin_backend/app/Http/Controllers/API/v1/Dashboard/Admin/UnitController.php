<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UnitResource;
use App\Http\Requests\DeleteAllRequest;
use App\Services\UnitService\UnitService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\UnitRepository\UnitRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends AdminBaseController
{

    public function __construct(
        protected Unit $model,
        protected UnitRepository $unitRepository,
        protected UnitService $unitService
    )
    {
        parent::__construct();
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $units = $this->unitRepository->unitsPaginate($request->perPage ?? 15, $request->active ?? null, $request->all());
        return UnitResource::collection($units);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $unit = $this->model->create([
                'active' => $request->active ?? 0,
                'position' => $request->position ?? 'after',
            ]);
            if ($unit){
                $unit->translations()->delete();

                foreach ($request->title as $index => $value){
                    $unit->translation()->create([
                        'locale' => $index,
                        'title' => $value,
                    ]);
                }
            }

            return $this->successResponse(__('web.record_successfully_created'), UnitResource::make($unit));

        } catch (Exception $exception) {
            return $this->errorResponse(
                ResponseError::ERROR_400, $exception->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $unit = $this->unitRepository->unitDetails($id);
        if ($unit){
            return $this->successResponse(__('web.unit_found'), UnitResource::make($unit->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $unit = $this->unitRepository->unitDetails($id);
        if ($unit){
            try {
                $result = $unit->update([
                    'active' => $request->active ?? 0,
                    'position' => $request->position ?? 'after',
                ]);

                if ($result){
                    $unit->translations()->delete();
                    foreach ($request->title as $index => $value){
                        $unit->translation()->create([
                            'locale' => $index,
                            'title' => $value,
                        ]);
                    }
                }
                return $this->successResponse(__('web.record_successfully_created'), UnitResource::make($unit));
            } catch (Exception $exception) {
                return $this->errorResponse(
                    ResponseError::ERROR_400, $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->unitService->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [],$this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function setActiveUnit($id): JsonResponse
    {
        $unit = $this->model->find($id);
        if ($unit) {
            $unit->update(['active' => !$unit->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), UnitResource::make($unit));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
