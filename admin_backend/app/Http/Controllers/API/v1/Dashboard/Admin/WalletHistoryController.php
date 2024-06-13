<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\WalletHistoryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletHistoryController extends AdminBaseController
{

    public function __construct
    (
        protected WalletHistoryService $walletHistoryService,
        protected WalletHistoryRepository $walletHistoryRepository
    )
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $walletHistory = $this->walletHistoryRepository->walletHistoryPaginate($request->perPage ?? 15, $request->all());
        return WalletHistoryResource::collection($walletHistory);
    }

    public function changeStatus(string $uuid, Request $request): JsonResponse
    {
        if (!isset($request->status) || !in_array($request->status, ['rejected', 'paid'])) {
            return $this->errorResponse(ResponseError::ERROR_253, trans('errors.' . ResponseError::ERROR_253, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }

        $result = $this->walletHistoryService->changeStatus($uuid, $request->status);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_updated'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }
}
