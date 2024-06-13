<?php

namespace App\Repositories\ProductRepository;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShopProduct;
use App\Repositories\CoreRepository;
use DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RestProductRepository extends CoreRepository
{

    private array $ids = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return ShopProduct::class;
    }

    public function findSecondRecursive($categories)
    {
        foreach ($categories as $category) {
            /** @var Category $category */
            $category = $category->load([
                'children'
            ]);

            $this->ids[] = $category->id;

            if (!empty($category->children) && count($category->children) > 0) {
                $this->ids[] = $category->children->pluck('id')->toArray();
                $this->findOneRecursive($category->children);
            }

        }
    }

    public function findOneRecursive($categories)
    {
        foreach ($categories as $category) {
            /** @var Category $category */
            $category = $category->load([
                'children'
            ]);

            $this->ids[] = $category->id;

            if (!empty($category->children) && count($category->children) > 0) {
                $this->ids[] = $category->children->pluck('id')->toArray();
                $this->findSecondRecursive($category->children);
            }

        }
    }

    public function prepareCategoryIds($categoryId)
    {

        /** @var Category $category */
        $category = Category::with([
            'children' => fn($q) => $q->select(['id', 'parent_id']),
        ])
            ->select(['id'])
            ->find($categoryId);


        $this->ids[] = $category->id;

        $this->findOneRecursive($category->children);

        $collectIds = [];

        foreach ($this->ids as $id) {

            if (is_array($id)) {
                $collectIds = array_merge($collectIds, $id);
            } else {
                $collectIds[] = $id;
            }

        }
        $this->ids = $collectIds;
    }

    public function productsPaginate($perPage, $active = null, $array = [])
    {
        $categoryId = data_get($array, 'category_id');

        if (!empty($categoryId)) {

            $this->prepareCategoryIds($categoryId);

            unset($array['category_id']);

            data_set($array, 'category_ids', array_unique($this->ids));
        }

        return $this->model()->filter($array)
            ->whereHas('product.translation')
            ->withAvg('reviews', 'rating')
            ->with([
                'product.translation:id,product_id,locale,title,description',
                'product.category:id,uuid',
                'product.category.translation:id,category_id,locale,title',
                'product.brand:id,uuid,title',
                'product.unit.translation',
                'shop.translation',
                'discount',
                'bonus' => fn($q) => $q->whereHas('bonusProduct', function ($query) {
                    $query->where('quantity', '>', 0);
                })
            ])
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->whereHas('product', function ($query) use ($array) {
                    $query->where('keywords', 'LIKE', '%' . $array['search'] . '%');
                })->orWhereHas('product.translations', function ($q) use ($array) {
                    $q->where('title', 'LIKE', '%' . $array['search'] . '%')
                        ->select('id', 'product_id', 'locale', 'title');
                });
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->when(isset($array['shop_slug']), function ($q) use ($array) {
                $q->whereHas('shop', function ($q) use ($array) {
                    $q->where('slug', $array['shop_slug']);
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->when(isset($array['gift']), function ($q) use ($array) {
                $q->whereHas('product', function ($q) {
                    $q->where('gift', 1);
                });
            })
            ->when(!isset($array['gift']) && !isset($array['my_gift_cart']), function ($q) use ($array) {
                $q->whereHas('product', function ($q) {
                    $q->where('gift', 0);
                });
            })
            ->when(isset($array['my_gift_cart']) && auth('sanctum')->user()?->id, function ($q) use ($array) {
                $userId = auth('sanctum')->user()->id;
                $q->whereHas('userGiftCarts', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->where('quantity','>',1)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function productsPaginateSearch($perPage, $active = null, $array = []): LengthAwarePaginator
    {
        /** @var ShopProduct $model */
        $model = $this->model();

        return $model->filter($array)
            ->withProductTranslations(['lang' => $this->language])
            ->when(data_get($array, 'search'), function ($q, $search) {
                /** @var Product $q */
                $q->whereHas('product.translations', function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->select('id', 'product_id', 'locale', 'title');
                });
            })
            ->whereHas('shop', function ($q) {
                $q->where('active', 1)->where('open', 1);
            })
            ->when(data_get($array, 'shop_id'), function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when(isset($active), function ($q, $active) {
                $q->where('active', $active);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function productByUUID(string $uuid)
    {
        return $this->model()
            ->whereHas('product.translation')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withCount('orders')
            ->withCount('ordersWeek')
            ->with([
                'bonus' => fn($q) => $q->whereHas('bonusProduct', function ($query) {
                    $query->where('quantity', '>', 0);
                }),
                'bonus.bonusProduct.product.translation:id,product_id,locale,title',
                'product.galleries:id,type,loadable_id,path,title',
                'product.properties',
                'shop.translation',
                'product.category:id,uuid',
                'product.category.translation:id,category_id,locale,title',
                'product.brand:id,uuid,title',
                'product.unit.translation',
                'reviews.galleries',
                'reviews.user',
                'product.translation',
                'discount',
                'product.translations',
                'shop.workingDays',
                'shop.closedDates',
            ])
            ->firstWhere('uuid', $uuid);
    }

    public function productBySlug(string $slug)
    {
        return $this->model()
            ->whereHas('product.translation')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withCount('orders')
            ->withCount('ordersWeek')
            ->with([
                'bonus' => fn($q) => $q->whereHas('bonusProduct', function ($query) {
                    $query->where('quantity', '>', 0);
                }),
                'bonus.bonusProduct.product.translation:id,product_id,locale,title',
                'product.galleries:id,type,loadable_id,path,title',
                'product.properties',
                'shop.translation',
                'product.category:id,uuid',
                'product.category.translation:id,category_id,locale,title',
                'product.brand:id,uuid,title',
                'product.unit.translation',
                'reviews.galleries',
                'reviews.user',
                'product.translation',
                'discount',
                'product.translations',
                'shop.workingDays',
                'shop.closedDates',
            ])
            ->whereHas('product', function ($q) use ($slug) {
                $q->where('slug', $slug);
            })
            ->first();
    }

    public function productsMostSold($perPage, $array = [])
    {
        return $this->model()->filter($array)
            ->withAvg('reviews', 'rating')
            ->withCount('orders')
            ->with([
                'product.translation:id,product_id,locale,title',
                'bonus:shop_product_id,id'
            ])
            ->whereActive(1)
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->orderBy('orders_count', 'desc')
            ->take(10)
            ->paginate($perPage);
    }

    /**
     * @param $perPage
     * @param array $array
     * @return mixed
     */
    public function productsDiscount($perPage, array $array = []): mixed
    {
        return $this->model()->filter($array)
            ->whereHas('discount')
            ->whereHas('product.translation', function ($q) {
                $q->where('locale', $this->language);
            })
            ->withAvg('reviews', 'rating')
            ->with([
                'product.translation:id,product_id,locale,title',
                'bonus:id,shop_product_id'
            ])->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->whereActive(1)
            ->paginate($perPage);
    }

    public function productsByIDs(array $ids)
    {
        return $this->model()->with([
            'product.translation:id,product_id,locale,title',
            'product.unit.translation',
            'discount'
        ])
            ->withAvg('reviews', 'rating')
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->find($ids);
    }

    public function buyWithProduct(int $id)
    {
        $shopId = $this->model->where('id', $id)->first()->shop_id;

        $orderIds = Order::whereHas('orderDetails', function ($q) use ($id) {
            $q->where('shop_product_id', $id);
        })->pluck('id');

        $productIds = DB::table('order_details as o_d')
            ->leftJoin('orders as o', 'o.id', '=', 'o_d.order_id')
            ->leftJoin('shop_products as sh_p', 'sh_p.id', '=', 'o_d.shop_product_id')
            ->select('shop_product_id', DB::raw('COUNT(shop_product_id) as shop_product_count'), 'sh_p.id')
            ->groupBy('shop_product_id')
            ->orderBy('shop_product_count', 'desc')
            ->whereIn('o.id', $orderIds)
            ->where('sh_p.id', '!=', $id)
            ->where('sh_p.quantity', '>', 0)
            ->where('sh_p.price', '>', 0)
            ->take(5)
            ->pluck('sh_p.id');

        return $this->model()->with([
            'product.translation',
            'product.unit.translation',
            'shop.translation'
        ])
            ->whereHas('shop', function ($item) use ($shopId) {
                $item->where('id', $shopId)->whereNull('deleted_at')->where('status', 'approved');
            })
            ->whereHas('product.translation')
            ->find($productIds);
    }
}
