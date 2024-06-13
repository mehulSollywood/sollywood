<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Resources\ShopProductResource;
use App\Repositories\DashboardRepository\DashboardRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DashboardController extends AdminBaseController
{

    public function __construct(protected DashboardRepository $repository)
    {
        parent::__construct();
    }

    public function countStatistics(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->statisticCount($request->all());
        return $this->successResponse(__('web.statistics_count'), $result);

    }

    public function sumStatistics(Request $request): JsonResponse|AnonymousResourceCollection
    {
//        $result = $this->repository->statisticSum($request->all());
        return $this->successResponse(__('web.statistics_sum'), []);
    }

    public function topCustomersStatistics(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->statisticTopCustomer($request->all());
        return $this->successResponse(__('web.statistics_top_customer'), UserResource::collection($result));
    }

    public function topProductsStatistics(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->statisticTopSoldProducts($request->all());
        return $this->successResponse(__('web.statistics_top_products'), ShopProductResource::collection($result));
    }

    public function ordersSalesStatistics(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->statisticOrdersSales($request->all());
        return $this->successResponse(__('web.statistics_orders_sales'), $result);
    }

    public function ordersCountStatistics(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->statisticOrdersCount($request->all());
        return $this->successResponse(__('web.statistics_order_count'), $result);
    }
}
