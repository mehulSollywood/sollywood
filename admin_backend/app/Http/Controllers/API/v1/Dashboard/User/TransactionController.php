<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\TransactionResource;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\TransactionRepository\TransactionRepository;

class TransactionController extends UserBaseController
{
    public function __construct(protected TransactionRepository $transactionRepository)
    {
        parent::__construct();
    }

    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $transactions = $this->transactionRepository->paginate($request->perPage ?? 15,
            $request->merge(['user_id' => auth('sanctum')->id()])->all());
        return TransactionResource::collection($transactions);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->transactionRepository->show($id);
        if ($transaction && $transaction->user_id == auth('sanctum')->id()) {
            return $this->successResponse(ResponseError::NO_ERROR, TransactionResource::make($transaction));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
