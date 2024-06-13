<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Helpers\ResponseError;
use App\Exports\WarehouseExport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\WarehouseResource;
use App\Http\Requests\FilterParamsRequest;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Services\WarehouseService\WarehouseService;
use App\Http\Requests\Seller\Warehouse\StoreRequest;
use App\Repositories\WarehouseRepository\WarehouseRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WarehouseController extends SellerBaseController
{
    public function __construct(protected WarehouseRepository $repository,protected WarehouseService $service)
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
        $collection = $request->validated();
        $model = $this->repository->paginate($collection['perPage']);
        return WarehouseResource::collection($model);
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
        $result = $this->service->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), WarehouseResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [],$this->language),
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
        $result = $this->repository->show($id);
        if ($result){
            return $this->successResponse(ResponseError::NO_ERROR, WarehouseResource::make($result));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(int $id): JsonResponse|AnonymousResourceCollection
    {

        $result = $this->service->destroy($id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public function fileExport(): JsonResponse
    {
        $shop_id = $this->shop->id;
        $time = Str::slug(Carbon::now()->format('Y-m-d h:i:s'));
        $fileName = 'export/' . $time . '-warehouse.xlsx';

        $file = Excel::store(new WarehouseExport($shop_id), $fileName, 'public');
        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => 'public/' . $fileName
            ]);
        }
        return $this->errorResponse('Error during export');
    }


}
