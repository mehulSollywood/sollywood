<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\User;
use App\Models\Settings;
use App\Models\ParcelOrder;
use App\Traits\Notification;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ParcelOrderResource;
use App\Http\Requests\User\AddReviewRequest;
use App\Http\Requests\User\ParcelOrder\StoreRequest;
use App\Services\ParcelOrderService\ParcelOrderService;
use App\Services\ParcelOrderService\ParcelOrderReviewService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ParcelOrderRepository\ParcelOrderRepository;
use App\Services\ParcelOrderService\ParcelOrderStatusUpdateService;

class ParcelOrderController extends UserBaseController
{
    use Notification;

    public function __construct(private ParcelOrderRepository $repository, private ParcelOrderService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $request->merge(['user_id' => auth('sanctum')->id()])->all();

        $orders = $this->repository->paginate($filter);

        return ParcelOrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ((int)data_get(Settings::adminSettings()->where('key', 'order_auto_approved')->first(), 'value') === 1) {
            $validated['status'] = ParcelOrder::STATUS_ACCEPTED;
        }

        $validated['user_id'] = auth('sanctum')->id();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

//        $tokens = $this->tokens();

//        $this->sendNotification(
//            data_get($tokens, 'tokens'),
//            "New parcel order was created",
//            data_get($result, 'data.id'),
//            data_get($result, 'data')?->setAttribute('type', PushNotification::NEW_PARCEL_ORDER)?->only(['id', 'status', 'type']),
//            data_get($tokens, 'ids', [])
//        );

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderResource::make(data_get($result, 'data'))
        );
    }

    public function tokens(): array
    {
        $adminFirebaseTokens = User::with([
            'roles' => fn($q) => $q->where('name', 'admin')
        ])
            ->whereHas('roles', fn($q) => $q->where('name', 'admin') )
            ->whereNotNull('firebase_token')
            ->pluck('firebase_token', 'id')
            ->toArray();

        $aTokens = [];

        foreach ($adminFirebaseTokens as $adminToken) {
            $aTokens = array_merge($aTokens, is_array($adminToken) ? array_values($adminToken) : [$adminToken]);
        }

        return [
            'tokens' => array_values(array_unique($aTokens)),
            'ids'    => array_keys($adminFirebaseTokens)
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param ParcelOrder $parcelOrder
     * @return JsonResponse
     */
    public function show(ParcelOrder $parcelOrder): JsonResponse
    {
        if ($parcelOrder->user_id !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        return $this->successResponse(
            ResponseError::NO_ERROR,
            ParcelOrderResource::make($this->repository->showByModel($parcelOrder))
        );
    }

    /**
     * @param int $id
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function orderStatusChange(int $id, FilterParamsRequest $request): JsonResponse
    {
        if (!$request->input('status')) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_254,
                'message' => __('errors.' . ResponseError::ERROR_254, locale: $this->language)
            ]);
        }

        /** @var ParcelOrder $parcelOrder */
        $parcelOrder = ParcelOrder::with([
            'deliveryman',
            'user.wallet',
        ])->find($id);

        if (!$parcelOrder) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $result = (new ParcelOrderStatusUpdateService)->statusUpdate($parcelOrder, $request->input('status'));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            ResponseError::NO_ERROR,
            ParcelOrderResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Add Review to Deliveryman.
     *
     * @param int $id
     * @param AddReviewRequest $request
     * @return JsonResponse
     */
    public function addDeliverymanReview(int $id, AddReviewRequest $request): JsonResponse
    {
        $result = (new ParcelOrderReviewService)->addDeliverymanReview($id, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            ResponseError::NO_ERROR,
            ParcelOrderResource::make(data_get($result, 'data'))
        );
    }
}
