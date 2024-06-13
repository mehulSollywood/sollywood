<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Referral;
use App\Models\WalletHistory;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ReferralResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\WalletHistoryResource;
use App\Services\ReferralService\ReferralService;
use App\Http\Requests\Admin\Referral\StoreRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferralController extends AdminBaseController
{

    public function __construct(protected ReferralService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $referral = Referral::with([
            'translation',
            'translations',
            'galleries',
        ])->get();

        return ReferralResource::collection($referral);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function transactions(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $referralTransactions = WalletHistory::with([
            'transaction',
        ])
            ->when($request->input('status'), fn($q, $status) => $q->where('status', $status))
            ->whereIn('type', [
                    'referral_from_topup', 'referral_to_topup', 'referral_from_withdraw', 'referral_to_withdraw'
                ]
            )
            ->paginate($request->input('perPage', 10));

        return WalletHistoryResource::collection($referralTransactions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->service->create($request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('web.record_has_been_successfully_created'),
            ReferralResource::make(data_get($result, 'data'))
        );

    }

    /**
     * Display a listing of the resource.
     *
     * @param Referral $referral
     * @return ReferralResource
     */
    public function show(Referral $referral): ReferralResource
    {
        return ReferralResource::make($referral->load([
            'translation',
            'translations',
            'galleries',
        ]));
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $this->service->delete($request->input('ids', []));

        return $this->successResponse(__('web.record_has_been_successfully_delete'), []);
    }
}
