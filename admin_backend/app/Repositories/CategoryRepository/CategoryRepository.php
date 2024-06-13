<?php

namespace App\Repositories\CategoryRepository;

use App\Exports\CategoryReportExport;
use App\Models\Category;
use App\Models\Currency;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\CategoryRepoInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

class CategoryRepository extends CoreRepository implements CategoryRepoInterface
{

    /**
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Category::class;
    }

    /**
     * Get Parent, only categories where parent_id == 0
     */
    public function parentCategories($perPage, $active = null, array $array = [])
    {
        return $this->model()
            ->whereHas('translation')
            ->filter($array)
            ->where('parent_id', null)
            ->with([
                'translation:id,locale,title,category_id',
                'children.translation:id,locale,title,category_id',
                'children.children.translation:id,locale,title,category_id'
            ])
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->whereHas('shopCategory', function ($query) use ($array) {
                    $query->where('shop_id', $array['shop_id']);
                });
            })
            ->when(isset($array['shop_slug']), function ($q) use ($array) {
                $q->whereHas('shopCategory.shop', function ($query) use ($array) {
                    $query->where('slug', $array['shop_slug']);
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Get Parent, only categories where parent_id == 0
     */
    public function selectPaginate($perPage, $active = null, array $array = [])
    {
        return $this->model()
            ->select([
                'id',
                'uuid',
                'parent_id',
                'active'
            ])
            ->whereHas('translation')
            ->filter($array)
            ->where('parent_id', null)
            ->with([
                'translation:id,locale,title,category_id',
                'children:id,uuid,parent_id,active',
                'children.translation:id,locale,title,category_id',
                'children.children.translation:id,locale,title,category_id'
            ])
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->whereHas('shopCategory', function ($query) use ($array) {
                    $query->where('shop_id', $array['shop_id']);
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Get categories with pagination
     */
    public function categoriesPaginate($perPage = 15, $active = null, $array = [])
    {
        return $this->model()
            ->with([
                'translation:id,locale,title,category_id',
                'children.translation:id,locale,title,category_id',
            ])
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->paginate($perPage);
    }

    /**
     * Get all categories list
     */
    public function categoriesList($array = [])
    {
        return $this->model()
            ->with([
                'parent.translation',
                'translation:id,locale,title,category_id'
            ])
            ->orderByDesc('id')->get();
    }

    /**
     * Get one category by Identification number
     */
    public function categoryDetails(int $id)
    {
        return $this->model()
            ->with([
                'parent.translation',
                'translation'
            ])
            ->find($id);
    }

    /**
     * Get one category by slug
     */
    public function categoryByUuid($uuid)
    {
        return $this->model()
            ->where('uuid', $uuid)
            ->withCount('products')
            ->with([
                'translation',
                'children.translation'
            ])
            ->first();
    }

    /**
     * Get one category by slug
     */
    public function categoryBySlug($slug)
    {
        return $this->model()
            ->where('slug', $slug)
            ->withCount('products')
            ->with([
                'translation',
                'children.translation'
            ])
            ->first();
    }

    public function categoriesSearch(string $search, $active = null, $shop_id = null)
    {
        return $this->model()->with([
            'translation'
        ])
            ->where(function ($query) use ($search) {
                $query->whereHas('translations',function ($q) use ($search){
                    $q->where('title', 'LIKE', '%' . $search . '%');
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->when(isset($shop_id), function ($q) use ($shop_id) {
                $q->whereHas('shopCategory', function ($query) use ($shop_id) {
                    $query->where('shop_id', $shop_id);
                });
            })
            ->where('parent_id','!=',null)
            ->latest()->take(50)->get();
    }

    public function shopCategoryById(int $id, int $shop_id)
    {
        return $this->model()
            ->with('translations')
            ->whereHas('shopCategory', function ($q) use ($shop_id) {
                $q->where('shop_id', $shop_id);
            })->orderByDesc('id')->find($id);
    }

    public function parentCategory()
    {
        return $this->model()
            ->with([
                'translation'
            ])
            ->ordeByDesc('id')
            ->get();
    }

    //Bu methodda shop o`ziga tortib olmagan categorylar chiqadi

    public function shopCategoryNonExistPaginate(int $shop_id, $array, $perPage)
    {
        $shopCategoryIds = $this->model()
            ->whereHas('shopCategory', function ($q) use ($shop_id) {
                $q->where('shop_id', $shop_id);
            })->pluck('id');

        return $this->model()
            ->with([
                'translation'
            ])
            ->whereNotIn('id', $shopCategoryIds)
            ->where('active', 1)
            ->filter($array)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    // Bu methodda shop o`ziga tegishli bo`lgan category productlarni oladi.

    /** @todo Проверить скорость работы говно метода */
    public function shopCategoryProduct($array, $perPage): LengthAwarePaginator
    {
        /** @var Category $categories */
        $categories = $this->model();

        $page = data_get($array, 'page') ?: Paginator::resolveCurrentPage('links');

        $category = $categories->with([
            'translation'
        ])
            ->whereHas('children.shopProduct', function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id'])->where('active',1);
            })
            ->whereHas('translation')
            ->where('parent_id', null);
        $categoriesCount = count($category->get());

        $parenCategories = $category->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()->each(function ($query) use ($array) {
                $shop = collect();
                return $query->children->each(function ($q) use ($query, $array, $shop) {
                    $shopProduct = $q->shopProduct->where('active',1)->where('shop_id', $array['shop_id'])->load([
                        'product.translation',
                        'product.unit.translation:id,locale,title,unit_id',
                        'bonus' => fn($q) => $q->whereHas('bonusProduct', function ($query) {
                            $query->where('quantity', '>', 0);
                        }),
                    ])->take(10);
                    if (count($shopProduct) > 0) {
                        $shop->push($shopProduct);
                        $query->setRelation('shopProduct', $shop->collapse()->take(10));
                    }
                });
            })->reject(function ($value) {
                return empty($value);
            });
        return new LengthAwarePaginator(
            $parenCategories,
            $categoriesCount,
            $perPage,
            data_get($array, 'page', 1),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'links',
            ]
        );
    }

    public function childrenCategory($perPage, int $id)
    {
        return $this->model()->with([
            'translation:id,locale,title,category_id',
            'children.translation:id,locale,title,category_id'
        ])->where('parent_id', null)->where('id', $id)->paginate($perPage);
    }

    /**
     * @param array $filter
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function reportChart(array $filter = []): array
    {
        $cacheFrom = date('Y-m', strtotime(data_get($filter, 'date_from')));
        $cacheTo = date('Y-m', strtotime(data_get($filter, 'date_to', now())));
        $dateFrom = date('Y-m-d 00:00:01', strtotime(data_get($filter, 'date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime(data_get($filter, 'date_to', now())));
        $type = data_get($filter, 'type');
        $chartType = data_get($filter, 'chart', 'count');

        Cache::delete("category-report-chart-$cacheFrom-$cacheTo");

        $paginate = Cache::remember("category-report-chart-$cacheFrom-$cacheTo", 86400, function () use ($dateFrom, $dateTo, $filter) {

            $categories = Category::filter($filter)->with([

                'translation:id,locale,title,category_id',

                'shopProductWithTrashed',

                'shopProductWithTrashed.orders:id,order_id,shop_product_id,quantity,created_at',

                'shopProductWithTrashed.orders.orderWithTrashed:id,price',

                'childrenWithTrashed:id,parent_id,type',

                'childrenWithTrashed.translation:id,locale,title,category_id',

                'childrenWithTrashed.childrenWithTrashed:id,parent_id,type',

                'childrenWithTrashed.childrenWithTrashed.translation',

                'childrenWithTrashed.shopProductWithTrashed',

                'childrenWithTrashed.shopProductWithTrashed.orders:id,order_id,shop_product_id,quantity,created_at',

                'childrenWithTrashed.shopProductWithTrashed.orders.orderWithTrashed:id,price',

                'childrenWithTrashed.childrenWithTrashed.shopProductWithTrashed',

                'childrenWithTrashed.childrenWithTrashed.childrenWithTrashed.orders:id,order_id,shop_product_id,quantity,created_at',

                'childrenWithTrashed.childrenWithTrashed.productsWithTrashed.shopProductWithTrashed.orders.orderWithTrashed:id,price',
            ])
                ->where(
                    'parent_id', null
                )
                ->withTrashed()
                ->where('created_at', '>=', $dateFrom)
                ->where('created_at', '<=', $dateTo)
                ->select([
                    'id',
                    'parent_id',
                    'type',
                    'created_at',
                    'deleted_at',
                ])
                ->get();

            $paginate = [];

            foreach ($categories as $category) {

                $key = $category->id;

                if (!data_get($paginate, $key)) {

                    $paginate[$key] = [
                        'id' => $category->id,
                        'created_at' => date('Y-m-d', strtotime($category->created_at)),
                        'title' => data_get($category->translation, 'title'),
                        'deleted_at' => date('Y-m-d', strtotime($category->deleted_at)),
                        'quantity' => 0,
                        'price' => 0,
                        'products_count' => 0,
                        'count' => 0,
                    ];

                }

                foreach ($category->childrenWithTrashed as $children) {

                    $this->reportPaginateData($children, $key, $paginate);

                    foreach ($children->childrenWithTrashed as $child) {

                        $this->reportPaginateData($child, $key, $paginate);

                    }

                }

            }
            return $paginate;
        });

        $paginate = collect(array_values($paginate))
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo);


        $chart = [];

        foreach ($paginate as $item) {

            $time = data_get($item, 'created_at');

            if ($type === 'year') {
                $time = date('Y', strtotime($time));
            } else if ($time === 'month') {
                $time = date('Y-m', strtotime($time));
            }

            if (empty($time)) {
                continue;
            }

            if (!data_get($chart, $time)) {

                $chart[$time] = [
                    'time' => $time,
                    $chartType => round(data_get($item, $chartType), 2)
                ];

                continue;
            }

            $value = data_get($chart, "$time.$chartType") + data_get($item, $chartType);

            $chart[$time] = [
                'time' => $time,
                $chartType => round($value, 2)
            ];

        }

        if (data_get($filter, 'export') === 'excel') {
            try {
                $name = 'categories-report-' . Str::random(8);

                Excel::store(new CategoryReportExport($paginate), "export/$name.xlsx", 'public');

                return [
                    'path' => 'public/export',
                    'file_name' => "export/$name.xlsx",
                    'link' => URL::to("storage/export/$name.xlsx"),
                ];
            } catch (Throwable $e) {
                $this->error($e);
                return [
                    'status' => false,
                    'message' => 'Cant export category'
                ];
            }
        }

        return [
            'paginate' => $paginate,
            'chart' => array_values($chart),
            'currency' => Currency::currenciesList()->where('id', $this->currency)->first(),
            'total_quantity' => $paginate->sum('quantity'),
            'total_price' => $paginate->sum('price'),
            'total_count' => $paginate->sum('count'),
            'total_products_count' => $paginate->sum('products_count'),
        ];
    }

    /**
     * @param Category|Collection[] $category
     * @param $key
     * @param $paginate
     * @return void
     */
    private function reportPaginateData(array|Category $category, $key, &$paginate): void
    {
        $title = data_get($paginate, "$key.title", '');
        $categoryTitle = data_get($category->translation, 'title');

        if (!empty($categoryTitle)) {
            data_set($paginate, "$key.title", "$title -> $categoryTitle");
        }

        $stockCount = data_get($paginate, "$key.products_count", 0);

        $products = $category->shopProductWithTrashed;

        data_set($paginate, "$key.products_count", $stockCount + $products->count());

        foreach ($products as $product) {

            $quantity = data_get($paginate, "$key.quantity", 0);
            $price = data_get($paginate, "$key.price", 0);
            $count = data_get($paginate, "$key.count", 0);

            $orderDetails = $product->orders;

            if ($orderDetails->count() === 0) {
                continue;
            }

            $firstCreatedAt = data_get($paginate, "$key.created_at");

            $createdAt = date('Y-m-d', strtotime($orderDetails->min('created_at')));

            if ($createdAt < $firstCreatedAt) {
                data_set($paginatem, "$key.created_at", $createdAt);
            }

            $sumQuantity = $orderDetails->sum('quantity');
            $sumPrice = $orderDetails->sum('order.price');
            $sumCount = $orderDetails->groupBy('order_id')->count();

            data_set($paginate, "$key.count", $count + $sumCount);
            data_set($paginate, "$key.price", $price + $sumPrice);
            data_set($paginate, "$key.quantity", $quantity + $sumQuantity);
        }

    }
}
