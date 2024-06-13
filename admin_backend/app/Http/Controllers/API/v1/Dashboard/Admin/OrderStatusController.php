<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderStatusResource;
use App\Services\OrderService\OrderStatusService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderStatusController extends AdminBaseController
{
    public function __construct(private OrderStatusService $service)
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
        $orderStatuses = OrderStatus::list()
            ->when($request->input('sort'), fn(Collection $q) =>
            $q->sortBy('sort', SORT_REGULAR, !($request->input('sort') === 'asc'))
            )
            ->when(isset($request['active']), fn(Collection $q) => $q->where('active', $request['active']))
            ->all();

        return OrderStatusResource::collection($orderStatuses);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function active(int $id, Request $request): JsonResponse
    {
        $result = $this->service->setActive($id, $request->all());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
    }
}
