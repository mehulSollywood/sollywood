<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\User;
use App\Models\Refund;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RefundResource;
use App\Http\Requests\Refund\IndexRequest;
use App\Http\Requests\Refund\StoreRequest;
use App\Http\Requests\Refund\UpdateRequest;
use App\Services\RefundService\RefundService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\RefundRepository\RefundRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RefundController extends UserBaseController
{

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

        $user = auth('sanctum')->user();

        $refunds = $this->repository->paginate($collection, null, $user);

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
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        /** @var User $user */

        $collection = $request->validated();

        $collection['user_id'] = $user->id;

        $collection['status'] = Refund::PENDING;

        $result = $this->service->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), RefundResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
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


}
