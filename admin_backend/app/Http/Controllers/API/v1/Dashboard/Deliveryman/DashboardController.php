<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\DashboardRepository\DashboardRepository;

class DashboardController extends DeliverymanBaseController
{
    public function __construct(protected Order $model)
    {
        parent::__construct();
    }

    public function countStatistics(Request $request): JsonResponse
    {
        return $this->successResponse(
            __('web.statistics_count'),
            (new DashboardRepository)->orderByStatusStatistics(
                $request->merge(['deliveryman' => auth('sanctum')->id()])->all()
            )
        );
    }
}
