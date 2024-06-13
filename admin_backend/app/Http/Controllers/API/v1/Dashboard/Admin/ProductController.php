<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\ProductResource;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductSearchResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ProductService\ProductService;
use App\Http\Requests\Product\FileImportRequest;
use App\Http\Requests\Admin\Product\ExportRequest;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Http\Requests\Admin\Product\OrderChartRequest;
use App\Services\ProductService\ProductAdditionalService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends AdminBaseController
{

    public function __construct(protected ProductService $productService, protected ProductRepoInterface $productRepository)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $products = $this->productRepository->productsPaginate($request->perPage ?? 15, $request->active, $request->all());
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->productService->create($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            return $this->successResponse(__('web.product_found'), ProductResource::make($product->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
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
        $result = $this->productService->update($id, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_update'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function destroy(string $uuid): JsonResponse
    {
        $result = $this->productService->destroy($uuid);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductProperties(string $uuid, Request $request): JsonResponse
    {
        $result = (new ProductAdditionalService())->createOrUpdateProperties($uuid, $request->all());

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
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
    public function productsSearch(Request $request): AnonymousResourceCollection
    {
        $categories = $this->productRepository->productsSearch($request->input('perPage', 15), true, $request->all());
        return ProductSearchResource::collection($categories);
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function setActive(string $uuid): JsonResponse
    {
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            $product->update(['active' => !$product->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), ProductResource::make($product));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function fileExport(ExportRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $time = Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

        $fileName = 'export/' . $time . '-products.xlsx';

        $file = Excel::store(new ProductsExport($collection['shop_id'] ?? null), $fileName, 'public');
        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => $fileName
            ]);
        }
        return $this->errorResponse('Error during export');
    }

    public function fileImport(FileImportRequest $request)
    {
        $collection = $request->validated();
        $shopId = $collection['shop_id'] ?? null;
        try {
            Excel::import(new ProductsImport($shopId), $collection['file']);
            return $this->successResponse('Successfully imported');
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_508, 'Excel format incorrect or data invalid');
        }
    }

    public function deleteAll(Request $request): JsonResponse
    {
        $result = $this->productService->deleteAll($request->productIds);
        if ($result)
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function reportChart(OrderChartRequest $request): JsonResponse
    {
        $collection = $request->validated();
        try {
            $result = $this->productRepository->reportChart($collection);

            return $this->successResponse('Successfully', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }


    public function reportPaginate(OrderChartRequest $request): JsonResponse
    {
        try {
            $result = $this->productRepository->productReportPaginate($request->all());

            return $this->successResponse(
                'Successfully',
                data_get($result, 'data')
            );
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

}
