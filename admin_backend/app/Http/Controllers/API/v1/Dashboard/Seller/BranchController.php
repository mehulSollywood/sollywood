<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BranchResource;
use App\Http\Requests\DeleteAllRequest;
use App\Services\BranchService\BranchService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Seller\Branch\StoreRequest;
use App\Http\Requests\Seller\Branch\UpdateRequest;
use App\Repositories\BranchRepository\BranchRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController extends SellerBaseController
{

    public function __construct(protected BranchRepository $repository,protected BranchService $service)
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $branches = $this->repository->paginate($request->perPage ?? 15);
        return BranchResource::collection($branches);
    }

    /**
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->service->create($collection, $this->shop->id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), BranchResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $branch = $this->repository->getById($id);
        if ($branch) {
            return $this->successResponse(__('web.coupon_found'), BranchResource::make($branch));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * @param int $id
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->service->update($id, $collection, $this->shop->id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), BranchResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
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
}
