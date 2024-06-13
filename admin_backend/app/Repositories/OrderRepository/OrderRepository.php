<?php

namespace App\Repositories\OrderRepository;

use App\Exports\OrderReportExport;
use App\Exports\OrdersRevenueReportExport;
use App\Helpers\ResponseError;
use App\Http\Resources\OrderResource;
use App\Models\Delivery;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\ChartService\ChartService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class OrderRepository extends CoreRepository implements OrderRepoInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Order::class;
    }

    public function ordersList(array $array = [])
    {
        return $this->model()->with('orderDetails.products')
            ->orderByDesc('id')
            ->where('created_at','<',now()->addDay(1))
            ->filter($array)->get();
    }

    public function ordersPaginate(int $perPage, int $userId = null, array $array = [])
    {
        return $this->model()->withCount('orderDetails')
            ->with([
                'orderDetails:id',
                'user:id,firstname,lastname,img',
                'bonusShop',
                'deliveryMan:id,firstname,lastname,phone',
                'transaction.paymentSystem.payment.translation',
                'shop:id,logo_img',
                'shop.translation:shop_id,id,title,locale',
                'currency:id,title,symbol,rate',
                'refund:id,order_id,user_id,status'
            ])
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id'])->with('shop');
            })
            ->when(isset($array['refund']), function ($q) use ($array) {
                $q->whereHas('refund');
            })
            ->when(isset($array['auto_order']),function ($q) use ($array){
                $q->where('auto_order',$array['auto_order']);
            })
            ->filter($array)
            ->when(isset($userId), function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('created_at','<',now()->addDay(1))
            ->orderBy('id', 'desc')->paginate($perPage);
    }

    public function show(int $id, $shopId = null)
    {
        return $this->model()
            ->with([
                'user',
                'currency:id,title,symbol',
                'deliveryType.translation',
                'deliveryAddress',
                'deliveryMan',
                'coupon',
                'shop.translation',
                'transaction.paymentSystem.payment:id,tag,active',
                'transaction.paymentSystem.payment.translation:id,locale,payment_id,title',
                'orderDetails.shopProduct.product.translation:id,locale,product_id,title',
                'orderDetails.shopProduct.product.unit.translation:id,locale,unit_id,title',
                'orderDetails.shopProduct.discount',
                'reviews',
                'branch.translation',
                'bonusShop.shopProduct.product.translation:id,locale,product_id,title',
                'galleries'
            ])
            ->when(isset($shopId), function ($q) use ($shopId) {
                $q->where('shop_id', $shopId)->with('shop');
            })
            ->find($id);
    }

    public function getById(int $id)
    {
        return $this->model()->find($id);
    }

    public function getByStatus($array)
    {
//        dd($currentPage);
        return $this->model()
            ->withCount('orderDetails')
            ->whereHas('shop')
            ->with([
                'shop' => fn($q) => $q->select('id', 'logo_img'),
                'shop.translation' => fn($q) => $q->select('shop_id', 'title'),
                'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'email'),
                'deliveryMan' => fn($q) => $q->select('id', 'firstname', 'lastname', 'email'),
                'transaction.paymentSystem.payment' => function ($q) {
                    $q->select('id', 'tag', 'active');
                }
            ])
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->where('id', 'LIKE', '%' . $array['search'] . '%')
                    ->orWhere('price', 'LIKE', '%' . $array['search'] . '%');
            })
            ->select('id', 'price', 'deliveryman', 'status', 'shop_id', 'user_id')
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->get()
            ->groupBy('status')
            ->map(function ($q) {
                return $q->take(5);
            });
    }

    public function debitOrderTransactions(array $filter)
    {
        return $this->model()->with([
            'shop' => fn($q) => $q->select('id', 'logo_img'),
            'shop.translation' => fn($q) => $q->select('shop_id', 'title'),
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname', 'email'),
            'deliveryMan' => fn($q) => $q->select('id', 'firstname', 'lastname', 'email'),
            'transaction.paymentSystem.payment' => function ($q) {
                $q->select('id', 'tag', 'active');
            },
            'transaction.transaction' => function($q) {
                $q->where('payable_type',Transaction::class);
            },
            'transaction.transaction.paymentSystem.payment' => function ($q) {
                $q->select('id', 'tag', 'active');
            }
        ])->when(isset($filter['deliveryman_id']), function ($q) use ($filter) {
            $q->whereHas('deliveryType', function ($q) {
                $q->where('type', '!=', Delivery::TYPE_PICKUP);
            })->where('deliveryman', $filter['deliveryman_id']);
        })
            ->when(isset($filter['shop_id']), function ($q) use ($filter) {
                $q->whereHas('deliveryType', function ($q) {
                    $q->where('type', Delivery::TYPE_PICKUP);
                })->where('shop_id', $filter['shop_id']);
            })
            ->when(isset($filter['status']), function ($q) use ($filter) {
                $q->whereHas('transaction', function ($q) use ($filter) {
                    $q->where('request', $filter['status']);
                });
            })
            ->whereHas('transaction.paymentSystem.payment',function ($q){
                $q->where('tag','cash');
            })
            ->whereHas('transaction', function ($q) {
                $q->whereNotNull('request');
            })
            ->where('created_at','<',now()->addDay(1))
            ->orderByDesc('id')
            ->paginate($filter['perPage']);
    }

    public function ordersReportChart(array $filter): array
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $type = data_get($filter, 'type');
        $key = data_get($filter, 'chart', 'count');

        $orders = DB::table('orders as o')
            ->join('order_details as o_d', 'o.id', '=', 'o_d.order_id')
            ->join('shop_products as sh_p', 'sh_p.id', '=', 'o_d.shop_product_id')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->where('o.status', Order::DELIVERED)
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->select([
                DB::raw("(DATE_FORMAT(o.created_at, " . ($type == 'year' ? "'%Y" : ($type == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
                DB::raw("count(distinct o.id) as count"),
                DB::raw("sum(o_d.quantity) as quantity"),
                DB::raw("sum(distinct o.price) as price"),
                DB::raw("sum(distinct o.tax) as tax"),
                DB::raw("avg(o_d.quantity) as avg_quantity"),
                DB::raw("avg(distinct o.price) as avg_price"),
            ])
            ->groupBy('time')
            ->get();

        return [
            'chart' => ChartService::chart($orders, $key),
            'currency' => $this->currency,
            'count' => $orders->sum('count'),
            'price' => $orders->sum('price'),
            'quantity' => $orders->sum('quantity'),
            'tax' => $orders->sum('tax'),
            'avg_price' => $orders->sum('avg_price'),
            'avg_quantity' => $orders->sum('avg_quantity'),
        ];

    }

    /**
     * @param array $filter
     * @return array|Collection
     */
    public function ordersReportChartPaginate(array $filter): array|Collection
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $default = data_get(Language::where('default', 1)->first(['locale', 'default']), 'locale');
        $key = data_get($filter, 'column', 'id');
        $column = data_get([
            'id',
            'status',
            'firstname',
            'lastname',
            'active',
            'quantity',
            'price',
            'tax',
            'avg_quantity',
            'avg_price',
        ], $key, $key);

        $orders = DB::table('orders as o')
            ->join('order_details as o_d', 'o.id', '=', 'o_d.order_id')
            ->join('shop_products as sh_p', 'o_d.shop_product_id', '=', 'sh_p.id')
            ->leftJoin('users as u', 'o.user_id', '=', 'u.id')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->where('o.status', Order::DELIVERED)
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->select([
                DB::raw("o.created_at as created_at"),
                DB::raw("o.deleted_at as deleted_at"),
                DB::raw("o.id as id"),
                DB::raw("o.status as status"),
                DB::raw("u.firstname as firstname"),
                DB::raw("u.lastname as lastname"),
                DB::raw("u.active as active"),
                DB::raw("sum(o_d.quantity) as quantity"),
                DB::raw("sum(distinct o.price) as price"),
                DB::raw("sum(distinct o.tax) as tax"),
                DB::raw("avg(o_d.quantity) as avg_quantity"),
                DB::raw("avg(distinct o.price) as avg_price"),
            ])
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->groupBy('id');

        if (data_get($filter, 'export') === 'excel') {

            $name = 'orders-report-products-' . Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

            try {
                Excel::store(new OrderReportExport($orders->get()), "export/$name.xlsx", 'public');

                return [
                    'status' => true,
                    'code' => ResponseError::NO_ERROR,
                    'path' => 'public/export',
                    'file_name' => "export/$name.xlsx",
                    'link' => URL::to("storage/export/$name.xlsx"),
                ];
            } catch (\Throwable $e) {
                $this->error($e);
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_501,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $orders = $orders->paginate(data_get($filter, 'perPage', 10));

        foreach ($orders as $order) {

            $products = OrderDetail::with([
                'shopProduct.product.translation' => fn($q) => $q->select('id', 'product_id', 'locale', 'title')
                    ->where('locale', $this->language)->orWhere('locale', $default),
                'shopProduct.product' => fn($q) => $q->withTrashed(),
                'shopProduct' => fn($q) => $q->withTrashed(),
            ])
                ->where('order_id', $order->id)
                ->get()
                ->pluck('shopProduct.product.translation')->toArray();

            data_set($order, 'products', $products);
        }

        $isDesc = data_get($filter, 'sort', 'desc') === 'desc';

        return collect($orders)->sortBy($column, $isDesc ? SORT_DESC : SORT_ASC, $isDesc);
    }

    /**
     * @param array $filter
     * @return array
     */
    public function revenueReport(array $filter): array
    {
        $type = data_get($filter, 'type');
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $column = [
            'count',
            'tax',
            'delivery_fee',
            'canceled_sum',
            'delivered_sum',
            'total_price',
            'total_quantity',
            'delivered_avg',
            'time',
        ];

        if (!in_array(data_get($filter, 'column'), $column)) {
            $filter['column'] = 'time';
        }

        $paginate = DB::table('orders as o')
            ->join('order_details as o_d', 'o.id', '=', 'o_d.order_id')
            ->join('shop_products as sh_p', 'o_d.shop_product_id', '=', 'sh_p.id')
            ->leftJoin('users as u', 'o.user_id', '=', 'u.id')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->whereIn('o.status', [Order::DELIVERED])
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->selectRaw("
                count(distinct if(o.status = 'delivered', o.id, null)) as count,
                sum(distinct if(o.status = 'delivered', o.tax, null)) as tax,
                sum(distinct if(o.status = 'delivered', o.delivery_fee, null)) as delivery_fee,
                sum(distinct if(o.status = 'delivered', o.price, null)) as delivered_sum,
                sum(distinct o.price) as price,
                sum(o_d.quantity) as total_quantity,
                avg(distinct if(o.status = 'delivered', o.price, null)) as delivered_avg,
                (DATE_FORMAT(o.created_at, " . ($type == 'year' ? "'%Y" : ($type == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time
            ")
            ->groupBy('time')
            ->orderBy(data_get($filter, 'column', 'time'), data_get($filter, 'sort', 'desc'))
            ->get();

        if (data_get($filter, 'export') === 'excel') {

            $name = 'report-revenue-' . Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

            try {
//                ExportJob::dispatch("export/$name.xlsx", $paginate, OrdersRevenueReportExport::class);
                Excel::store(new OrdersRevenueReportExport($paginate), "export/$name.xlsx", 'public');

                return [
                    'status' => true,
                    'code' => ResponseError::NO_ERROR,
                    'path' => 'public/export',
                    'file_name' => "export/$name.xlsx",
                    'link' => URL::to("storage/export/$name.xlsx"),
                ];
            } catch (Throwable $e) {
                $this->error($e);
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_501,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'paginate' => $paginate,
            'total_price' => $paginate->sum('price'),
            'total_quantity' => $paginate->sum('total_quantity'),
            'delivered_sum' => $paginate->sum('delivered_sum'),
            'total_tax' => $paginate->sum('tax'),
            'total_count' => $paginate->sum('count'),
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    public function overviewCarts(array $filter): array
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $type = data_get($filter, 'type');
        $column = [
            'count',
            'tax',
            'delivery_fee',
            'canceled_sum',
            'delivered_sum',
            'delivered_avg',
            'time',
        ];

        if (!in_array(data_get($filter, 'column'), $column)) {
            $filter['column'] = 'time';
        }

        $chart = DB::table('orders as o')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->whereIn('o.status', [Order::DELIVERED, Order::CANCELED])
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->join('order_details as o_d', 'o.id', '=', 'o_d.order_id')
            ->join('shop_products as sh_p', 'o_d.shop_product_id', '=', 'sh_p.id')
            ->selectRaw("
                count(if(o.status = 'delivered', o.id, 0)) as count,
                sum(if(o.status = 'delivered', o.tax, 0)) as tax,
                sum(if(o.status = 'delivered', o.delivery_fee, 0)) as delivery_fee,
                sum(if(o.status = 'canceled', o.price, 0)) as canceled_sum,
                sum(if(o.status = 'delivered', o.price, 0)) as delivered_sum,
                avg(if(o.status = 'delivered', o.price, 0)) as delivered_avg,
                (DATE_FORMAT(o.created_at, " . ($type == 'year' ? "'%Y" : ($type == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time
            ")
            ->groupBy('time')
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort', 'desc'))
            ->get();

        return [
            'chart_price' => ChartService::chart($chart, 'delivered_sum'),
            'chart_count' => ChartService::chart($chart, 'count'),
            'count' => $chart->sum('count'),
            'tax' => $chart->sum('tax'),
            'delivery_fee' => $chart->sum('delivery_fee'),
            'canceled_sum' => $chart->sum('canceled_sum'),
            'delivered_sum' => $chart->sum('delivered_sum'),
            'delivered_avg' => $chart->sum('delivered_avg'),
        ];

    }


    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function overviewProducts(array $filter): LengthAwarePaginator
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $default = data_get(Language::where('default', 1)->first(['locale', 'default']), 'locale');
        $key = data_get($filter, 'column', 'count');
        $shopId = data_get($filter, 'shop_id');
        $column = data_get([
            'title',
            'id',
            'count',
            'total_price',
        ], $key, $key);

        if ($column == 'id') {
            $column = 'sh_p.id';
        }

        return DB::table('shop_products as sh_p')
            ->crossJoin('product_translations as p_t', 'sh_p.product_id', '=', 'p_t.product_id')
            ->crossJoin('shop_translations as sh_t', 'sh_p.shop_id', '=', 'sh_t.shop_id')
            ->crossJoin('order_details as o_d', 'sh_p.id', '=', 'o_d.shop_product_id')
            ->crossJoin('orders as o', function ($builder) use ($shopId, $dateFrom, $dateTo) {
                $builder->on('o_d.order_id', '=', 'o.id')
                    ->when($shopId, fn($q) => $q->where('o.shop_id', $shopId))
                    ->where('o.created_at', '>=', $dateFrom)
                    ->where('o.created_at', '<=', $dateTo)
                    ->whereIn('o.status', [Order::DELIVERED, Order::CANCELED]);
            })
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->where(fn($q) => $q->where('p_t.locale', '=', $this->language)->orWhere('p_t.locale', $default))
            ->where(fn($q) => $q->where('sh_t.locale', '=', $this->language)->orWhere('sh_t.locale', $default))
            ->whereIn('o.status', [Order::DELIVERED, Order::CANCELED])
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->select([
                DB::raw("p_t.title as shop_title"),
                DB::raw("sh_t.title as title"),
                DB::raw("sh_p.id as id"),
                DB::raw("count(if(o.status = 'delivered', o.id, 0)) as count"),
                DB::raw("sum(if(o.status = 'delivered', o.price, 0)) as total_price"),
            ])
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->groupBy(['title', 'id', 'shop_title'])
            ->having('count', '>', '0')
            ->orHaving('total_price', '>', '0')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function overviewCategories(array $filter): LengthAwarePaginator
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $default = data_get(Language::where('default', 1)->first(['locale', 'default']), 'locale');

        $column = data_get([
            'title',
            'id',
            'count',
            'total_price',
        ], data_get($filter, 'column', 'count'), 'count');

        return DB::table('shop_products as sh_p')
            ->join('products as p', 'p.id', '=', 'sh_p.product_id')
            ->join('category_translations as c_t', 'p.category_id', '=', 'c_t.category_id')
            ->join('order_details as o_d', 'sh_p.id', '=', 'o_d.shop_product_id')
            ->join('orders as o', 'o_d.order_id', '=', 'o.id')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->where(fn($q) => $q->where('c_t.locale', '=', $this->language)->orWhere('locale', $default))
            ->whereIn('o.status', [Order::DELIVERED, Order::CANCELED])
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->select([
                DB::raw("c_t.title as title"),
                DB::raw("p.category_id as id"),
                DB::raw("sum(if(o.status = 'delivered', 1, 0)) as count"),
                DB::raw("sum(if(o.status = 'delivered', o.price, 0)) as total_price"),
            ])
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->groupBy(['title', 'id'])
            ->having('count', '>', '0')
            ->orHaving('total_price', '>', '0')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param Order|null $order
     * @return OrderResource|null
     */
    public function reDataOrder(?Order $order): OrderResource|null
    {
        return !empty($order) ? OrderResource::make($order) : null;
    }

    public function deliveryManReport(array $filter = []): array
    {
        $type = data_get($filter, 'type', 'day');
        $dateFrom = date('Y-m-d 00:00:01', strtotime(request('date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(request('date_to', now())));
        $now = now()?->format('Y-m-d 00:00:01');
        $user = User::withAvg('assignReviews', 'rating')
            ->with(['wallet'])
            ->find(data_get($filter, 'deliveryman'));

        $lastOrder = DB::table('orders')
            ->where('deliveryman', data_get($filter, 'deliveryman'))
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        $orders = DB::table('orders')
            ->where('deliveryman', data_get($filter, 'deliveryman'))
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereNull('deleted_at')
            ->select([
                DB::raw("sum(if(status = 'delivered', delivery_fee, 0)) as delivery_fee"),
                DB::raw('count(id) as total_count'),
                DB::raw("sum(if(created_at >= '$now', 1, 0)) as total_today_count"),
                DB::raw("sum(if(status = 'new', 1, 0)) as total_new_count"),
                DB::raw("sum(if(status = 'ready', 1, 0)) as total_ready_count"),
                DB::raw("sum(if(status = 'on_a_way', 1, 0)) as total_on_a_way_count"),
                DB::raw("sum(if(status = 'accepted', 1, 0)) as total_accepted_count"),
                DB::raw("sum(if(status = 'canceled', 1, 0)) as total_canceled_count"),
                DB::raw("sum(if(status = 'delivered', 1, 0)) as total_delivered_count"),
            ])
            ->first();

        $type = match ($type) {
            'year' => '%Y',
            'week' => '%w',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $chart = DB::table('orders')
            ->where('deliveryman', data_get($filter, 'deliveryman'))
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereNull('deleted_at')
            ->where('status', Order::DELIVERED)
            ->select([
                DB::raw("(DATE_FORMAT(created_at, '$type')) as time"),
                DB::raw('sum(delivery_fee) as total_price'),
            ])
            ->groupBy('time')
            ->orderBy('time')
            ->get();

        return [
            'last_order_total_price' => (int)ceil(data_get($lastOrder, 'total_price', 0)),
            'last_order_income' => (int)ceil(data_get($lastOrder, 'delivery_fee', 0)),
            'total_price' => (int)data_get($orders, 'delivery_fee', 0),
            'avg_rating' => $user->assign_reviews_avg_rating,
            'wallet_price' => $user->wallet?->price,
            'wallet_currency' => $user->wallet?->currency,
            'total_count' => (int)data_get($orders, 'total_count', 0),
            'total_today_count' => (int)data_get($orders, 'total_today_count', 0),
            'total_new_count' => (int)data_get($orders, 'total_new_count', 0),
            'total_ready_count' => (int)data_get($orders, 'total_ready_count', 0),
            'total_on_a_way_count' => (int)data_get($orders, 'total_on_a_way_count', 0),
            'total_accepted_count' => (int)data_get($orders, 'total_accepted_count', 0),
            'total_canceled_count' => (int)data_get($orders, 'total_canceled_count', 0),
            'total_delivered_count' => (int)data_get($orders, 'total_delivered_count', 0),
            'chart' => $chart
        ];
    }

    /**
     * @param array $filter
     * @return array
     */
    public function orderByStatusStatistics(array $filter = []): array
    {
        $filter['status'] = 'all';

        $delivered = Order::DELIVERED;
        $canceled = Order::CANCELED;
        $new = Order::NEW;
        $accepted = Order::ACCEPTED;
        $ready = Order::READY;
        $onAWay = Order::ON_A_WAY;
        $date = date('Y-m-d 00:00:01');

        $result = [
            'count' => 0,
            'total_price' => 0,
            'delivered' => 0,
            'cancel' => 0,
            'new' => 0,
            'accepted' => 0,
            'ready' => 0,
            'on_a_way' => 0,
            'today_count' => 0,
            'total_delivered_price' => 0,
        ];

//        $filter['date_from'] = date('Y-m-d H:i:s', strtotime('-1 minute'));

        Order::filter($filter)
            ->select(['id', 'price', 'status', 'created_at'])
            ->chunkMap(function (Order $order) use (&$result, $date, $delivered, $canceled, $new, $accepted, $ready, $onAWay) {

                $result['count'] += 1;
                $result['total_price'] += $order->price;
                if ($order->status === Order::DELIVERED) {
                    $result['total_delivered_price'] += $order->price;
                }

                if ($order->created_at >= $date) {
                    $result['today_count'] += 1;
                }

                switch ($order->status) {
                    case $delivered:
                        $result[$delivered] += 1;
                        break;
                    case $canceled:
                        $result['cancel'] += 1;
                        break;
                    case $new:
                        $result[$new] += 1;
                        break;
                    case $accepted:
                        $result[$accepted] += 1;
                        break;
                    case $ready:
                        $result[$ready] += 1;
                        break;
                    case $onAWay:
                        $result[$onAWay] += 1;
                        break;
                }

                return true;
            });

        $progress = data_get($result, 'new', 0) + data_get($result, 'accepted', 0) +
            data_get($result, 'ready', 0) + data_get($result, 'on_a_way', 0);

        return [
            'progress_orders_count' => $progress,
            'delivered_orders_count' => data_get($result, 'delivered'),
            'total_delivered_price' => data_get($result, 'total_delivered_price'),
            'cancel_orders_count' => data_get($result, 'cancel'),
            'new_orders_count' => data_get($result, 'new'),
            'accepted_orders_count' => data_get($result, 'accepted'),
            'ready_orders_count' => data_get($result, 'ready'),
            'on_a_way_orders_count' => data_get($result, 'on_a_way'),
            'orders_count' => data_get($result, 'count'),
            'total_price' => data_get($result, 'total_price'),
            'today_count' => data_get($result, 'today_count'),
        ];
    }

    public function orderChartPaginate(array $filter = []): array
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $type = data_get($filter, 'type');
        $key = data_get($filter, 'chart', 'price');

        $orders = DB::table('orders as o')
            ->join('order_details as o_d', 'o.id', '=', 'o_d.order_id')
            ->join('shop_products as sh_p', 'sh_p.id', '=', 'o_d.shop_product_id')
            ->whereNull('sh_p.deleted_at')
            ->whereNull('o.deleted_at')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->where('o.status', Order::DELIVERED)
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->select([
                DB::raw("(DATE_FORMAT(o.created_at, " . ($type == 'year' ? "'%Y" : ($type == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
                DB::raw("count(distinct o.id) as count"),
                DB::raw("avg(distinct o.price) as avg_price"),
                DB::raw("sum(distinct o.price) as price"),
                DB::raw("sum(distinct o.delivery_fee) as total_delivery_fee"),
                DB::raw("sum(distinct o.tax) as tax"),
            ])
            ->groupBy('time')
            ->get();

        $orderByStatus = DB::table('orders as o')
            ->when(data_get($filter, 'shop_id'), fn($q, $shopId) => $q->where('o.shop_id', $shopId))
            ->whereNull('o.deleted_at')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->select([
                DB::raw("count(o.id) as total_order"),
                DB::raw("sum(if(o.status = 'delivered', 1, 0)) as delivered"),
                DB::raw("sum(if(o.status = 'new', 1, 0)) as new"),
                DB::raw("sum(if(o.status = 'canceled', 1, 0)) as canceled"),
                DB::raw("sum(if(o.status = 'accepted', 1, 0)) as accepted"),
            ])
            ->first();

        return [
            'chart' => ChartService::chart($orders, $key),
            'accepted_orders_count' => $orderByStatus->accepted,
            'delivered_orders_count' => $orderByStatus->delivered,
            'cancel_orders_count' => $orderByStatus->canceled,
            'new_orders_count' => $orderByStatus->new,
            'orders_count' => $orderByStatus->total_order,
            'total_delivered_price' => $orders->sum('price'),
            'total_delivery_fee' => $orders->sum('total_delivery_fee'),
            'order_tax' => $orders->sum('tax'),
        ];
    }

    public function billingReport($filter)
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $key = data_get($filter, 'column', 'id');
        $column = data_get([
            'id',
            'price',
            'tax',
            'commission_fee',
            'delivery_fee',
        ], $key, $key);

        $orders = DB::table('orders as o')
            ->where('o.created_at', '>=', $dateFrom)
            ->where('o.created_at', '<=', $dateTo)
            ->where('o.status', Order::DELIVERED)
            ->select([
                DB::raw("o.created_at as created_at"),
                DB::raw("o.deleted_at as deleted_at"),
                DB::raw("o.id as id"),
                DB::raw("o.price as price"),
                DB::raw("o.tax as tax"),
                DB::raw("o.commission_fee as commission_fee"),
            ])
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->groupBy('id');
        if (data_get($filter, 'export') === 'excel') {

            $name = 'orders-report-products-' . Str::slug(Carbon::now()->format('Y-m-d h:i:s'));

            try {
                Excel::store(new OrderReportExport($orders->get()), "export/$name.xlsx", 'public');

                return [
                    'status' => true,
                    'code' => ResponseError::NO_ERROR,
                    'path' => 'public/export',
                    'file_name' => "export/$name.xlsx",
                    'link' => URL::to("storage/export/$name.xlsx"),
                ];
            } catch (\Throwable $e) {
                $this->error($e);
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_501,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $orders = $orders->paginate(data_get($filter, 'perPage', 10));

    }

}
