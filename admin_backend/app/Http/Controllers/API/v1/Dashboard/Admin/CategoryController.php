<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use App\Exports\CategoryExport;
use App\Imports\CategoryImport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\DeleteAllRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\CategoryCreateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CategoryServices\CategoryService;
use App\Http\Requests\Admin\Product\OrderChartRequest;
use App\Repositories\Interfaces\CategoryRepoInterface;
use App\Http\Requests\Admin\Category\FileImportRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends AdminBaseController
{

    public function __construct(
        protected CategoryService $categoryService,
        protected CategoryRepoInterface $categoryRepository
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryRepository->parentCategories($request->all());
        return $this->successResponse(__('web.categories_list'), CategoryResource::collection($categories));
    }

    /**
     * Display a listing of the resource with paginate.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryRepository->parentCategories($request->perPage ?? 15, null, $request->all());
        return CategoryResource::collection($categories);
    }

    /**
     * Display a listing of the resource with paginate.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function selectPaginate(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryRepository->selectPaginate($request->perPage ?? 15, null, $request->all());
        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CategoryCreateRequest $request
     * @return JsonResponse
     */
    public function store(CategoryCreateRequest $request): JsonResponse
    {
       // dd($request->referralPercentage);
        $result = $this->categoryService->create($request);
       
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $category = $this->categoryRepository->categoryByUuid($uuid);
        if ($category){
            $category->load('translations')->makeHidden('translation');
            return $this->successResponse(__('web.category_found'), CategoryResource::make($category));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CategoryCreateRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(string $uuid, CategoryCreateRequest $request): JsonResponse
    {
        $result = $this->categoryService->update($uuid, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), []);
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

        $result = $this->categoryService->destroy($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove Model image from storage.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function imageDelete(string $uuid): JsonResponse
    {
        $category = Category::firstWhere('uuid', $uuid);
        if ($category) {
            Storage::disk('public')->delete($category->img);
            $category->update(['img' => null]);

            return $this->successResponse(__('web.image_has_been_successfully_delete'), $category);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function categoriesSearch(Request $request): AnonymousResourceCollection
    {
        $categories = $this->categoryRepository->categoriesSearch($request->search ?? '',shop_id: $request->shop_id ?? null);
        return CategoryResource::collection($categories);
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $category = $this->categoryRepository->categoryByUuid($uuid);
        if ($category) {
            $category->update(['active' => !$category->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), CategoryResource::make($category));
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
    public function fileExport(): JsonResponse|AnonymousResourceCollection
    {
        $time = Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

        $fileName = 'export/'. $time .'-categories.xls';
        $file = Excel::store(new CategoryExport(), $fileName, 'public');
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
            Excel::import(new CategoryImport(), $collection['file']);
            return $this->successResponse('Successfully imported');
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_508,'Excel format incorrect or data invalid');
        }
    }

    public function reportChart(OrderChartRequest $request): JsonResponse
    {
        try {
            $result = $this->categoryRepository->reportChart($request->all());

            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
