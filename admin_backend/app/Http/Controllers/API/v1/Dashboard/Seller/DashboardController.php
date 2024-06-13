<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Resources\ShopProductResource;
use App\Repositories\DashboardRepository\DashboardRepository;

class DashboardController extends SellerBaseController
{

    public function __construct(protected DashboardRepository $repository)
    {
        parent::__construct();
    }

    public function countStatistics(Request $request): JsonResponse
    {
        $result = $this->repository->statisticCount($request->merge(['shop_id' => $this->shop->id])->all());
        return $this->successResponse(__('web.statistics_count'), $result);

    }

    public function sumStatistics(): JsonResponse
    {
//        $result = $this->repository->statisticSum($request->merge(['shop_id' => $this->shop->id])->all());
        return $this->successResponse(__('web.statistics_sum'),[]);
    }

    public function topCustomersStatistics(Request $request): JsonResponse
    {
        $result = $this->repository->statisticTopCustomer($request->merge(['shop_id' => $this->shop->id])->all());
        return $this->successResponse(__('web.statistics_top_customer'), UserResource::collection($result));
    }

    public function topProductsStatistics(Request $request): JsonResponse
    {
        $result = $this->repository->statisticTopSoldProducts($request->merge(['shop_id' => $this->shop->id])->all());
        return $this->successResponse(__('web.statistics_top_products'), ShopProductResource::collection($result));
    }

    public function ordersSalesStatistics(Request $request): JsonResponse
    {
        $result = $this->repository->statisticOrdersSales($request->merge(['shop_id' => $this->shop->id])->all());
        return $this->successResponse(__('web.statistics_orders_sales'), $result);
    }

    public function ordersCountStatistics(Request $request): JsonResponse
    {
        $result = $this->repository->statisticOrdersCount($request->merge(['shop_id' => $this->shop->id])->all());
        return $this->successResponse(__('web.statistics_order_count'), $result);
    }
}
