<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryUploadRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use App\Services\GalleryService\FileStorageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class GalleryController extends Controller
{
    use ApiResponse;

    public function __construct(private Gallery $model,private FileStorageService $storageService)
    {
        $this->middleware(['sanctum.check', 'role:admin|seller|moderator|manager|deliveryman'])->except('store');
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function getStorageFiles(): JsonResponse
    {
        $type = \request()->type ?? null;
        $length = \request()->length ?? null;
        $start = \request()->start ?? 0;

        if (!in_array($type, Gallery::TYPES)){
            return $this->errorResponse(ResponseError::ERROR_413, trans('errors.ERROR_413',  [], $this->language), Response::HTTP_NOT_FOUND);
        }
        $files = $this->storageService->getStorageFiles($type, $length, $start);
        return $this->successResponse(__('web.list_of_storage_files'), $files);
    }

    /**
     * Destroy a file from the storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteStorageFile(Request $request): JsonResponse
    {
        $result = $this->storageService->deleteFileFromStorage($request->file);
        if ($result['status']){
            return $this->successResponse(trans('web.successfully_deleted',  [], $this->language), $result['data']);
        }
        return $this->errorResponse($result['code'], trans('errors.' . $result['code'],  [], $this->language), Response::HTTP_NOT_FOUND);

    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $galleries = $this->model->orderByDesc('id')->paginate($request->perPage ?? 15);

        $galleries->map(function ($gallery){
            $file = Storage::disk('public')->exists('/images/' . $gallery->path);
            if ($file){
                $gallery->isset = true;
            }
        });

        return  GalleryResource::collection($galleries);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param GalleryUploadRequest $request
     * @return JsonResponse
     */
    public function store(GalleryUploadRequest $request): JsonResponse
    {
            $result = FileHelper::uploadFile($request->image, $request->type ?? 'unknown', 400, 400);

            if ($result['status']) {
                return $this->successResponse(
                    trans('web.image_successfully_uploaded', [], $this->language),
                    ['title' => $result['data'], 'type' => $request->type]
                );
            }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }


}
