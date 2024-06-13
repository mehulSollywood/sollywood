<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use DB;
use Throwable;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\PointHistory;
use App\Models\WalletHistory;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\Wallet\StoreRequest;
use App\Services\WalletService\WalletService;
use App\Http\Resources\WalletHistoryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\Wallet\ShareRequest;
use App\Services\TransactionService\TransactionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\WalletHistoryService\WalletHistoryService;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletController extends UserBaseController
{

    public function __construct(
        protected WalletHistoryRepository $walletHistoryRepository,
        protected WalletService  $walletService,
        protected WalletHistoryService $walletHistoryService
    )
    {
        parent::__construct();
    }

    public function walletHistories(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $histories = $this->walletHistoryRepository->walletHistoryPaginate($request->perPage ?? 15, $request->all());
        return WalletHistoryResource::collection($histories);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        /** @var User $user */

        $collection = $request->validated();

        $user = auth('sanctum')->user();

        $collection['type'] = $collection['type'] ?? 'withdraw';

        if ($user->wallet) {
            if ($collection['type'] == 'withdraw' && $user->wallet->price < $collection['price']) {
                return $this->errorResponse(
                    ResponseError::ERROR_109, trans('errors.' . ResponseError::ERROR_109, [], $this->language),
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                $result = $this->walletHistoryService->create($user, $request->all());
                if ($result['status']) {
                    return $this->successResponse(__('web.record_was_successfully_create'), WalletHistoryResource::make($result['data']));
                }
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_108, trans('errors.' . ResponseError::ERROR_108, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function changeStatus(string $uuid, Request $request): JsonResponse
    {
        if (!$request->input('status') ||
            !in_array($request->input('status'), [WalletHistory::REJECTED, WalletHistory::CANCELED])) {
            return $this->errorResponse(ResponseError::ERROR_253, trans('errors.' . ResponseError::ERROR_253, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        $result = $this->walletHistoryService->changeStatus($uuid, $request->status ?? null);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_updated'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function pointHistories(FilterParamsRequest $request): LengthAwarePaginator
    {
        return PointHistory::where('user_id', auth('sanctum')->id())
            ->orderBy($request->column ?? 'created_at', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);
    }

    /**
     * @throws Throwable
     */
    public function share(ShareRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $user = User::find(auth('sanctum')->id());

        if (!$user->wallet){
            return $this->errorResponse(ResponseError::ERROR_114, trans('errors.' . ResponseError::ERROR_114, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($user->wallet->price < $collection['price']){
            return $this->errorResponse(ResponseError::ERROR_109, trans('errors.' . ResponseError::ERROR_109, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }

        $wallet = Wallet::firstWhere('code',$collection['code']);

        if (!$wallet){
            return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }

        $wallet->update([
            'price' => $wallet->price + $collection['price']
        ]);

        $user->wallet->update([
            'price' => $user->wallet->price - $collection['price']
        ]);

        $shareUser = User::find($wallet->user_id);

        if (!$shareUser){
            return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        DB::beginTransaction();

        $collection['type']   = WalletHistory::TOP_UP;
        $collection['status'] = WalletHistory::PAID;

        (new WalletHistoryService)->create($shareUser,$collection);

        $collection['type']   = WalletHistory::WITHDRAW;
        $collection['status'] = WalletHistory::PAID;

        (new WalletHistoryService)->create($user,$collection);

        $request->user_id = auth('sanctum')->id();

        (new TransactionService)->walletTransaction($user->wallet->id,$request);

        $request->user_id = $wallet->user_id;

        (new TransactionService)->walletTransaction($wallet->id,$request);

        DB::commit();

        return $this->successResponse(__('web.success'), []);
    }


}
