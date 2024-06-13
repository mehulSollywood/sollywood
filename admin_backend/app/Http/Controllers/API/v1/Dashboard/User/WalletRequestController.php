<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use Throwable;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\WalletRequestResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\WalletRequest\ChangeRequest;
use App\Http\Requests\User\WalletRequest\StoreRequest;
use App\Http\Requests\User\WalletRequest\UpdateRequest;
use App\Services\WalletRequestService\WalletRequestService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\WalletRequestRepository\WalletRequestRepository;

class WalletRequestController extends UserBaseController
{
    public function __construct(
        protected WalletRequestService    $service,
        protected WalletRequestRepository $repository,
    )
    {
        parent::__construct();
    }

    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $walletRequests = $this->repository->index($collection);
        return WalletRequestResource::collection($walletRequests);
    }

    public function show(int $id): JsonResponse
    {
        $walletRequest = $this->repository->show($id);
        if ($walletRequest) {
            return $this->successResponse(ResponseError::NO_ERROR, WalletRequestResource::make($walletRequest));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->service->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->service->update($collection, $id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_update'));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function destroy(int $id): JsonResponse
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
     * @throws Throwable
     */
    public function changeStatus(ChangeRequest $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->service->changeStatus($collection, $id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_update'), WalletRequestResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

}
