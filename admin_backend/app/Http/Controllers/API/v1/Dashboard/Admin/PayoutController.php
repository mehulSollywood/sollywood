<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Payout;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PayoutResource;
use App\Http\Requests\FilterParamsRequest;
use App\Services\PayoutService\PayoutService;
use App\Http\Requests\Admin\Payout\StoreRequest;
use App\Http\Requests\Admin\Payout\UpdateRequest;
use App\Repositories\PayoutRepository\PayoutRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PayoutController extends AdminBaseController
{

    public function __construct(protected PayoutRepository $repository,protected PayoutService $service)
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
        $payouts = $this->repository->paginate($request->all());

        return PayoutResource::collection($payouts);
    }

    /**
     * NOT USED
     * Display the specified resource.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(ResponseError::NO_ERROR, []);
    }

    /**
     * Display the specified resource.
     *
     * @param Payout $payout
     * @return JsonResponse
     */
    public function show(Payout $payout): JsonResponse
    {
        $payout = $this->repository->show($payout);

        return $this->successResponse(ResponseError::NO_ERROR, PayoutResource::make($payout));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Payout $payout
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(Payout $payout, UpdateRequest $request): JsonResponse
    {
        $result = $this->service->update($payout, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_was_successfully_create'), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $id
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function statusChange(int $id, FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->statusChange($id, $request->input('status'));

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_was_successfully_update'), []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $this->service->destroy($request->input('ids', []));

        return $this->successResponse(__('web.record_has_been_successfully_delete'), []);
    }
}
