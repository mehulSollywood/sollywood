<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BlogResource;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\BlogRepository\BlogRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogController extends RestBaseController
{

    public function __construct(protected BlogRepository $blogRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $blogs = $this->blogRepository->blogsPaginate(
            $request->perPage ?? 15, true, $request->merge(['published_at' => true])
        );

        return BlogResource::collection($blogs);
    }

    /**
     * Find Blog by UUID.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $blog = $this->blogRepository->blogByUUID($uuid);
        if ($blog){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), BlogResource::make($blog));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
