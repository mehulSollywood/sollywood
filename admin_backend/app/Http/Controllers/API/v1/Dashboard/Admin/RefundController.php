<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Carbon\Carbon;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use App\Exports\RefundExport;
use App\Imports\RefundImport;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\RefundResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\Refund\IndexRequest;
use App\Http\Requests\Refund\UpdateRequest;
use App\Services\RefundService\RefundService;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Refund\StatisticsRequest;
use App\Http\Requests\Product\FileImportRequest;
use App\Http\Requests\Refund\StatusUpdateRequest;
use App\Repositories\RefundRepository\RefundRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RefundController extends AdminBaseController
{
    use ApiResponse;

    public function __construct(protected RefundRepository $repository,protected RefundService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource with paginate.
     *
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */

    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $refunds = $this->repository->paginate($collection);
        return RefundResource::collection($refunds);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $refund = $this->repository->show($id);

        if ($refund) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), RefundResource::make($refund));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->service->update($collection, $id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_update'), RefundResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param StatusUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function statusChange(StatusUpdateRequest $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->service->statusChange($collection, $id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_update'), RefundResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
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

        $result = $this->service->delete($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param StatisticsRequest $request
     * @return JsonResponse
     */
    public function statistics(StatisticsRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $statistics = $this->repository->statisticsAdmin($collection['lang']);
        return $this->successResponse(__('web.record_has_been_successfully_found'), $statistics);
    }

    /**
     * @return JsonResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function export(): JsonResponse
    {
        $fileName = 'export/refund'.Str::slug(Carbon::now()->format('Y-m-d h:i:s')).'.xls';
        $file = Excel::store(new RefundExport(), $fileName, 'public');
        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => $fileName
            ]);
        }
        return $this->errorResponse('Error during export');
    }

    /**
     * @param FileImportRequest $request
     * @return JsonResponse
     */
    public function import(FileImportRequest $request): JsonResponse
    {
        $collection = $request->validated();
        try {
            Excel::import(new RefundImport(), $collection['file']);
            return $this->successResponse('Successfully imported');
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_508, 'Excel format incorrect or data invalid');
        }
    }
}
