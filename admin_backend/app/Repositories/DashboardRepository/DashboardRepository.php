<?php

namespace App\Repositories\DashboardRepository;

use App\Models\Order;
use App\Models\Review;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use App\Repositories\CoreRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository extends CoreRepository
{

    protected function getModelClass(): string
    {
        return Order::class;
    }

    public function statisticCount($array = []): array
    {
        // GET ORDERS COUNT
        $order = DB::table('orders')
            ->select(DB::raw("sum(case when (status='new') then 1 else 0 end) as count_new_orders,
            sum(case when (status='accepted') then 1 else 0 end) as count_accepted_orders,
            sum(case when (status='ready') then 1 else 0 end) as count_ready_orders,
            sum(case when (status='on_a_way') then 1 else 0 end) as count_on_a_way_orders,
            sum(case when (status='delivered') then 1 else 0 end) as count_delivered_orders,
            sum(case when (status='canceled') then 1 else 0 end) as count_canceled_orders,
            sum(price) as total_earned,
            sum(delivery_fee) as delivery_earned,
            sum(tax) as tax_earned,
            sum(commission_fee) as commission_fee
        "))->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })->whereNull('deleted_at')->first();


        // GET PRODUCTS OUT OF STOCK COUNT
        $product = DB::table('shop_products as sh_p')
            ->select(DB::raw("sum(case when (sh_p.quantity = 0) then 1 else 0 end) as count_out_of_stock_product,
        count(sh_p.id) as count_product
        "))->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('sh_p.shop_id', $array['shop_id']);
            })
            ->leftJoin('products as p', 'p.id', '=', 'sh_p.product_id')
            ->whereNull('p.deleted_at')
            ->whereNull('sh_p.deleted_at')->first();

        // GET REVIEWS COUNT
        $reviews = Review::with('reviewable')
            ->where('reviewable_type', Order::class)
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('assignable_id', $array['shop_id'])
                    ->whereHasMorph('assignable', Shop::class);
            })->whereHas('reviewable')->count();
        return [
            'count_new_orders' => $order->count_new_orders,
            'count_accepted_orders' => $order->count_accepted_orders,
            'count_ready_orders' => $order->count_ready_orders,
            'count_on_a_way_orders' => $order->count_on_a_way_orders,
            'count_delivered_orders' => $order->count_delivered_orders,
            'count_canceled_orders' => $order->count_canceled_orders,
            'total_earned' => $order->total_earned,
            'delivery_earned' => $order->delivery_earned,
            'tax_earned' => $order->tax_earned,
            'commission_fee' => $order->commission_fee,
            'count_out_of_stock_product' => $product->count_out_of_stock_product,
            'count_product' => $product->count_product,
            'reviews' => $reviews,
        ];
    }

    public function statisticTopCustomer($array = []): Collection
    {
        $time = $array['time'] ?? 'subMonth';

        return User::whereDate('created_at', '>', now()->{$time}())
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->whereHas('orders', function ($q) use ($array) {
                    $q->where('shop_id', $array['shop_id']);
                })
                    ->withSum(['orders' => function ($q) use ($array) {
                        $q->where('shop_id', $array['shop_id']);
                    }], 'price');
            }, function ($q) {
                $q->withSum('orders', 'price');
            })
            ->orderByDesc('orders_sum_price')
            ->take(5)->get();
    }

    public function statisticTopSoldProducts($array = [])
    {
        $time = $array['time'] ?? 'subMonth';

        return ShopProduct::with([
            'product.category',
            'product.translation'
        ])->when(isset($array['shop_id']), function ($shop) use ($array) {
            $shop->where('shop_id', $array['shop_id']);
        })
            ->withCount('orders')
            ->where('active', 1)
            ->whereDate('created_at', '>', now()->{$time}())
            ->orderByDesc('orders_count')
            ->take(5)->get();
    }

    public function statisticOrdersSales($array = [])
    {
        $time = $array['time'] ?? 'subYear';

        return $this->model()
            ->whereDate('created_at', '>', now()->{$time}())
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->where('status', 'delivered')
            ->selectRaw('DATE(created_at) as date, ROUND(SUM(price), 2) as price')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    }

    public function statisticOrdersCount($array = [])
    {
        $time = $array['time'] ?? 'subYear';

        return $this->model()
            ->whereDate('created_at', '>', now()->{$time}())
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->when(isset($array['shop_id']), function ($shop) use ($array) {
                $shop->where('shop_id', $array['shop_id']);
            })
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    }

    public function orderByStatusStatistics(array $filter = []): array
    {
        $result = Order::filter($filter)
            ->select(DB::raw("sum(case when (status='new') then 1 else 0 end) as count_new_orders,
            sum(case when (status='accepted') then 1 else 0 end) as count_accepted_orders,
            sum(case when (status='ready') then 1 else 0 end) as count_ready_orders,
            sum(case when (status='on_a_way') then 1 else 0 end) as count_on_a_way_orders,
            sum(case when (status='delivered') then 1 else 0 end) as count_delivered_orders,
            sum(case when (status='canceled') then 1 else 0 end) as count_canceled_orders,
            sum(price) as total_earned,
            sum(delivery_fee) as delivery_earned,
            sum(tax) as tax_earned,
            sum(commission_fee) as commission_fee,
            count(id) as orders_count
        "))->whereNull('deleted_at')->first();

        return [
            'delivered_orders_count' => $result['count_delivered_orders'],
            'total_delivered_price' => $result['delivery_earned'],
            'cancel_orders_count' => $result['count_canceled_orders'],
            'new_orders_count' => $result['count_new_orders'],
            'accepted_orders_count' => $result['count_accepted_orders'],
            'ready_orders_count' => $result['count_ready_orders'],
            'on_a_way_orders_count' => $result['count_on_a_way_orders'],
            'total_price' => $result['total_earned'],
            'today_count' => $result['orders_count'],
        ];
    }
}
