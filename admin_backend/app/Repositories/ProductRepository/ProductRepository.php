<?php

namespace App\Repositories\ProductRepository;

use App\Exports\ProductReportExport;
use App\Helpers\ResponseError;
use App\Http\Resources\ProductReportResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShopProduct;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Services\ChartService\ChartService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ProductRepository extends CoreRepository implements ProductRepoInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function productsList($active = null, $array = [])
    {
        return $this->model()->whereHas('translation')
            ->with('translation')
            ->filter($array)
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function productsPaginate($perPage, $active = null, $array = [])
    {
        return $this->model()->filter($array)
            ->whereHas('translation')
            ->withAvg('reviews', 'rating')
            ->with([
                'translation:id,product_id,locale,title',
                'category:id,uuid',
                'category.translation:id,category_id,locale,title',
                'brand:id,uuid,title',
                'unit.translation',
            ])
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->whereHas('translations', function ($q) use ($array) {
                    $q->where('title', 'LIKE', '%' . $array['search'] . '%')
                        ->select('id', 'product_id', 'locale', 'title');
                });
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->whereHas('shopProduct', function ($q) use ($array) {
                    $q->where('shop_id', $array['shop_id']);
                });
            })
            ->when(isset($array['gift']), function ($q) use ($array) {
                $q->where('gift', 1);
            })
            ->when(!isset($array['gift']), function ($q) use ($array) {
                $q->where('gift', 0);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function productDetails(int $id)
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->language);
            })
            ->withAvg('reviews', 'rating')
            ->with([
                'galleries:id,type,loadable_id,path,title',
                'translation:id,product_id,locale,title',
                'category.translation:id,category_id,locale,title',
                'brand:id,uuid,title',
                'unit.translation',

            ])->find($id);
    }

    public function productByUUID(string $uuid)
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->language);
            })
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->with([
                'galleries:id,type,loadable_id,path,title',
                'properties',
                'category:id,uuid',
                'category.translation:id,category_id,locale,title',
                'brand:id,uuid,title',
                'unit.translation',
                'reviews.galleries',
                'reviews.user',
                'translation',
                'translations',
            ])
            ->firstWhere('uuid', $uuid);
    }

    public function productsByIDs(array $ids)
    {
        return $this->model()->with([
            'translation:id,product_id,locale,title',
        ])
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->find($ids);
    }

    public function productsSearch($perPage, $active = null, $array = []): LengthAwarePaginator
    {

        /** @var Product $model */
        $model = $this->model();

        return $model->filter($array)
            ->with([
                'translation:id,product_id,locale,title',
                'unit:id,active,position',
                'unit.translation',
            ])
            ->when(data_get($array, 'search'), function ($q, $search) {
                /** @var Product $q */
                $q->where('keywords', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('translations', function ($q) use ($search) {
                        $q->where('title', 'LIKE', '%' . $search . '%')
                            ->select('id', 'product_id', 'locale', 'title');
                    });

            })
            ->when(data_get($array, 'shop_id'), function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function shopProductsSearch($perPage, $active = null, $array = []): LengthAwarePaginator
    {

        $model = clone new ShopProduct;

        return $model->where('shop_id', data_get($array, 'shop.id'))
            ->filter($array)
            ->withProductTranslations(['lang' => $this->language])
            ->when(data_get($array, 'search'), function ($q, $search) {
                /** @var Product $q */
                $q->whereHas('product', function ($query) use ($search) {
                    $query->where('keywords', 'LIKE', '%' . $search . '%');
                })->orWhereHas('product.translations', function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->select('id', 'product_id', 'locale', 'title');
                });

            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    //Bu methodda shop o`ziga tortib olmagan productlar chiqadi

    public function shopProductNonExistPaginate(int $shop_id, $array, $perPage)
    {
        $shopProductIds = $this->model()->whereHas('shopProduct', function ($q) use ($shop_id) {
            $q->where('shop_id', $shop_id);
        })->pluck('id');
        return $this->model()
            ->whereHas('translation')
            ->with('translation')
            ->whereNotIn('id', $shopProductIds)
            ->when(isset($array['gift']), function ($q) use ($array) {
                $q->where('gift', 1);
            })
            ->when(!isset($array['gift']), function ($q) use ($array) {
                $q->where('gift', 0);
            })
            ->filter($array)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function reportChart(array $filter): array
    {
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now()->format('Y-m-d'))));
        $type = data_get($filter, 'type'); // year,month,day
        $chart = data_get($filter, 'chart', 'count'); // count,price,avg_price,avg_quantity,tax
        $orders = Order::withTrashed()
            ->leftJoin('order_details as o_d', 'o_d.order_id', '=', 'orders.id')
            ->where([
                ['orders.created_at', '>=', $dateFrom],
                ['orders.created_at', '<=', $dateTo],
                ['orders.status', '=', Order::DELIVERED],
            ])
            ->select([
                DB::raw("(DATE_FORMAT(orders.created_at, " . ($type == 'year' ? "'%Y" : ($type == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
                DB::raw('count(orders.id) as count'),
                DB::raw('sum(o_d.total_price) as price'),
            ])
            ->whereHas('orderDetails.shopProductsWithTrashed.productWithTrashed', function ($q) use ($filter) {
                $q->when(isset($filter['productIds']), function ($q) use ($filter) {
                    $q->whereIn('id', $filter['productIds']);
                })->when(isset($filter['category_id']), function ($q) use ($filter) {
                    $q->where('category_id', $filter['category_id']);
                });
            })
            ->when(isset($filter['shop_id']), function ($q) use ($filter) {
                $q->where('shop_id', $filter['shop_id']);
            })
            ->withSum('orderDetails', 'quantity')
            ->withSum('orderDetails', 'total_price')
            ->groupBy(['time', 'order_details_sum_quantity', 'order_details_sum_total_price'])
            ->get();

        $result = [];

        foreach ($orders as $order) {

            if (data_get($result, data_get($order, 'time'))) {
                $result[data_get($order, 'time')]['count'] += data_get($order, 'count', 0);
                $result[data_get($order, 'time')]['price'] += data_get($order, 'price', 0);
                $result[data_get($order, 'time')]['quantity'] += data_get($order, 'order_details_sum_quantity', 0);
                continue;
            }

            $result[data_get($order, 'time')] = [
                'time' => data_get($order, 'time'),
                'count' => data_get($order, 'count', 0),
                'price' => data_get($order, 'price', 0),
                'quantity' => data_get($order, 'order_details_sum_quantity', 0),
            ];

        }

        $result = collect(array_values($result));

        return [
            'chart' => ChartService::chart($result, $chart),
            'count' => $result->sum('count'),
            'price' => $result->sum('price'),
            'quantity' => $result->sum('quantity'),
        ];
    }

    public function productReportPaginate(array $filter): array
    {
        try {
            $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
            $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
            $key = data_get($filter, 'column', 'id');
            $column = data_get([
                'id',
                'bar_code',
                'category_id',
                'active',
                'shop_id',
                'deleted_at',
            ], $key, $key);

            $data = Product::withTrashed()->filter($filter)->with([
                'translation:id,product_id,locale,title',

                'category' => fn($q) => $q->withTrashed(),

                'category.translation',

                'shopProductsWithTrashed.orders' => fn($q) => $q
                    ->select('id', 'order_id', 'shop_product_id', 'total_price', 'tax', 'quantity'),
            ])
                ->when(isset($filter['shop_id']), function ($q) use ($filter) {
                    $q->whereHas('shopProductsWithTrashed', function ($q) use ($filter) {
                        $q->where('shop_id', $filter['shop_id']);
                    });
                })
                ->addSelect([
                    'count' => Order::whereHas('orderDetails.shopProductsWithTrashed', function ($q) {
                        $q->whereColumn('product_id', 'products.id');
                    })
                        ->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->where('status', Order::DELIVERED)
                        ->withTrashed()
                        ->selectRaw('IFNULL(COUNT(id), 0)'),

                    'quantity' => OrderDetail::whereHas('shopProductsWithTrashed', function ($q) {
                        $q->whereColumn('product_id', 'products.id');
                    })
                        ->whereHas('orderWithTrashed', function ($q) use ($dateFrom, $dateTo) {
                            $q->whereBetween('created_at', [$dateFrom, $dateTo])
                                ->where('status', Order::DELIVERED);
                        })
                        ->selectRaw('IFNULL(SUM(quantity), 0)'),

                    'price' => OrderDetail::whereHas('shopProductsWithTrashed', function ($q) {
                        $q->whereColumn('product_id', 'products.id');
                    })
                        ->whereHas('orderWithTrashed', function ($q) use ($dateFrom, $dateTo) {
                            $q->whereBetween('created_at', [$dateFrom, $dateTo])
                                ->where('status', Order::DELIVERED);
                        })
                        ->selectRaw('IFNULL(SUM(total_price), 0)')
                ])
                ->when(isset($filter['productIds']), fn($q) => $q->whereIn('id', $filter['productIds']))
                ->whereHas('shopProductsWithTrashed.orders.orderWithTrashed', function ($q) use ($dateFrom, $dateTo) {
                    $q->whereBetween('created_at', [$dateFrom, $dateTo])
                        ->where('status', Order::DELIVERED);
                })
                ->orderBy($column, data_get($filter, 'sort', 'desc'));

            if (data_get($filter, 'export') === 'excel') {

                $name = 'products-report-' . Str::random(8);

                $result = ProductReportResource::collection($data->get());

                Excel::store(new ProductReportExport($result), "export/$name.xlsx", 'public');

                return [
                    'status' => true,
                    'code' => ResponseError::NO_ERROR,
                    'data' => [
                        'path' => 'public/export',
                        'file_name' => "export/$name.xlsx",
                        'link' => URL::to("storage/export/$name.xlsx"),
                    ]
                ];

            }
            $data = $data->paginate(data_get($filter, 'perPage', 10));
            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => [
                    'data' => ProductReportResource::collection($data),
                    'meta' => [
                        'last_page' => $data->lastPage(),
                        'page' => $data->currentPage(),
                        'total' => $data->total(),
                        'more_pages' => $data->hasMorePages(),
                        'has_pages' => $data->hasPages(),
                    ]
                ],
            ];

        } catch (Throwable $e) {
            return [
                'status' => false,
                'code' => ResponseError::ERROR_400,
                'message' => $e->getMessage()
            ];
        }
    }
}

