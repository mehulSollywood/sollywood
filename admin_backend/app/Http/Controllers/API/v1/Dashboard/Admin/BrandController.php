<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Exports\BrandExport;
use Illuminate\Http\Request;
use App\Imports\BrandImport;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\BrandResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\BrandCreateRequest;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Interfaces\BrandServiceInterface;
use App\Http\Requests\Admin\Brand\FileImportRequest;
use App\Repositories\BrandRepository\BrandRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends AdminBaseController
{

    public function __construct(protected BrandRepository $brandRepository,protected BrandServiceInterface $brandService)
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
        $brands = $this->brandRepository->brandsList(request()->all());
        return $this->successResponse(__('web.brands_list'), BrandResource::collection($brands));
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $brands = $this->brandRepository->brandsPaginate($request->perPage ?? 15, null, $request->all());
        return BrandResource::collection($brands);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BrandCreateRequest $request
     * @return JsonResponse
     */
    public function store(BrandCreateRequest $request): JsonResponse
    {
        $result = $this->brandService->create($request);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BrandResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
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
        $brand = $this->brandRepository->brandDetails($id);

        if ($brand){
            return $this->successResponse(__('web.brand_found'), BrandResource::make($brand));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [],$this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param BrandCreateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, BrandCreateRequest $request): JsonResponse
    {
        $result = $this->brandService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), BrandResource::make($result['data']));
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

        $result = $this->brandService->destroy($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }


    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function brandsSearch(Request $request): AnonymousResourceCollection
    {
        $brands = $this->brandRepository->brandsSearch($request->search ?? '');
        return BrandResource::collection($brands);
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return BrandResource
     */
    public function setActive(int $id): BrandResource
    {
        $brand = $this->brandRepository->brandDetails($id);
        $brand->update(['active' => !$brand->active]);

        return BrandResource::make($brand);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function fileExport(): JsonResponse
    {
        $time = Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

        $fileName = 'export/'. $time .'-brands.xls';
        $file = Excel::store(new BrandExport(), $fileName, 'public');
        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => $fileName
            ]);
        }
        return $this->errorResponse('Error during export');
    }

    public function fileImport(FileImportRequest $request): JsonResponse
    {
        $collection = $request->validated();
        try {
            Excel::import(new BrandImport(), $collection['file']);
            return $this->successResponse('Successfully imported');
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_508,'Excel format incorrect or data invalid');
        }
    }

}
