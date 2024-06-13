<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use App\Imports\ProductsImport;
use Illuminate\Http\JsonResponse;
use App\Exports\ShopProductExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopProductResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\ShopProduct\StoreRequest;
use App\Http\Requests\Product\FileImportRequest;
use App\Http\Requests\ShopProduct\UpdateRequest;
use App\Http\Resources\ShopProductSelectResource;
use App\Services\ShopProductService\ShopProductService;
use App\Repositories\ProductRepository\ProductRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ShopProductRepository\ShopProductRepository;

class ShopProductController extends SellerBaseController
{

    public function __construct(
        protected ShopProductRepository $shopProductRepository,
        protected ShopProductService $shopProductService,
        protected ProductRepository $productRepository)
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
        $shopProducts = $this->shopProductRepository->paginate($this->shop->id, $request->all());

        return ShopProductResource::collection($shopProducts);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function selectProducts(Request $request): AnonymousResourceCollection
    {

        $shopProducts = $this->shopProductRepository->selectProducts($this->shop->id, $request->all());

        return ShopProductSelectResource::collection($shopProducts);
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {

        $shopProduct = $this->shopProductRepository->getById($id, $this->shop->id);

        return $this->successResponse(__('web.record_successfully_found'), $shopProduct);
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

        $collection['shop_id'] = $this->shop->id;
        $result = $this->shopProductService->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), $result['data']);
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], request('lang')),
            Response::HTTP_BAD_REQUEST
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
        $collection['shop_id'] = $this->shop->id;
        $result = $this->shopProductService->update($collection, $id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), $result['data']);
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
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();
        $result = $this->shopProductService->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function allProduct(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $product = $this->productRepository->shopProductNonExistPaginate($this->shop->id, $request->all(), $request->perPage ?? 15);
        if ($product) {
            return ProductResource::collection($product);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function getByUuid($uuid): JsonResponse
    {
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            return $this->successResponse(__('web.record_successfully_found'), ProductResource::make($product));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function fileExport(): JsonResponse
    {
        $time = Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

        $fileName = 'export/'. $time .'-shop-products.xls';

        $file = Excel::store(new ShopProductExport($this->shop), $fileName, 'public');
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
            Excel::import(new ProductsImport($this->shop->id), $collection['file']);
            return $this->successResponse('Successfully imported');
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_508, 'Excel format incorrect or data invalid');
        }
    }

}
