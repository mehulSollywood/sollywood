<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use App\Traits\SetCurrency;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\ShopProduct
 *
 * @property int $id
 * @property int $shop_id
 * @property int|null $min_qty
 * @property int|null $max_qty
 * @property int $active
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $product_id
 * @property Carbon|null $deleted_at
 * @property string|null $uuid
 * @property float $price
 * @property float|null $tax
 * @property-read BonusProduct|null $bonus
 * @property-read Discount|null $discount
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read mixed $actual_discount
 * @property-read mixed $discount_expired
 * @property-read mixed $tax_price
 * @property-read Collection|OrderDetail[] $orders
 * @property-read int|null $orders_count
 * @property-read int|null $orders_week_count
 * @property-read Product|null $product
 * @property-read Collection|Product[] $products
 * @property-read int|null $products_count
 * @property-read int|null $reviews_avg_rating
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read Shop|null $shop
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct withProductTranslations($array)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct newQuery()
 * @method static Builder|ShopProduct onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereMaxQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereMinQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopProduct whereUuid($value)
 * @method static Builder|ShopProduct withTrashed()
 * @method static Builder|ShopProduct withoutTrashed()
 * @mixin Eloquent
 */
class ShopProduct extends Model
{
    use HasFactory, SoftDeletes, Loadable, Reviewable, SetCurrency;

    protected bool $softDelete = true;


    /**
     * @var mixed
     */
    protected $fillable = [
        'product_id',
        'shop_id',
        'min_qty',
        'max_qty',
        'active',
        'quantity',
        'price',
        'uuid',
        'tax',
    ];
    protected $guarded = [];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'id', 'product_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function productWithTrashed(): BelongsTo
    {
        return $this->belongsTo(Product::class,'product_id','id')->withTrashed();
    }

    public function userGiftCarts(): HasMany
    {
        return $this->hasMany(UserGiftCart::class,'shop_product_id','id');
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'shop_product_id', 'id');
    }

    public function ordersWeek(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'shop_product_id', 'id')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    public function discount(): HasOneThrough
    {
        return $this->hasOneThrough(Discount::class, ProductDiscount::class, 'shop_product_id',
            'id', 'id', 'discount_id')
            ->whereDate('start', '<=', today())->whereDate('end', '>=', today())
            ->where('active', 1)->orderByDesc('id');
    }

    public function bonus(): HasOne
    {
        return $this->hasOne(BonusProduct::class)
            ->where('status', true)
            ->whereDate('expired_at', '>=', now());
    }

    public function getPriceAttribute($value)
    {
        return $value * $this->currency();
    }

    public function getActualDiscountAttribute($value)
    {
        if (isset($this->discount->type)) {
            if ($this->discount->type == 'percent') {
                $price = $this->discount->price / 100 * $this->price;
            } else {
                $price = $this->discount->price * $this->currency();
            }
            return $price;
        }
        return 0;
    }

    public function getDiscountExpiredAttribute($value): ?string
    {
        return $this->discount->end ?? null;
    }

    public function getTaxPriceAttribute($value)
    {
        return (($this->price - $this->actual_discount) / 100) * $this->tax;
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['price_from']), function ($q) use ($array) {
                $q->whereBetween('price', [$array['price_from'], $array['price_to'] ?? 10000000000]);
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->when(isset($array['column_rate']), function ($q) use ($array) {
                $q->whereHas('reviews', function ($review) use ($array) {
                    $review->orderBy('rating', $array['sort']);
                });
            })
            ->when(isset($array['category_id']), function ($query) use ($array) {
                $query->whereHas('product', function ($q) use ($array) {
                    $q->where('category_id', $array['category_id']);
                });
            })
            ->when(isset($array['category_ids']), function ($query) use ($array) {
                $query->whereHas('product.category', function ($q) use ($array) {
                    $q->whereIn('category_id', $array['category_ids']);
                });
            })
            ->when(isset($array['brand_id']), function ($query) use ($array) {
                $query->whereHas('product', function ($q) use ($array) {
                    $q->where('brand_id', $array['brand_id']);
                });
            })
            ->when(isset($array['column_order']), function ($q) use ($array) {
                $q->withCount('orders')->orderBy('orders_count', $array['sort']);
            })
            ->when(data_get($array, 'search'), function ($q, $search) {
                /** @var Product $q */
                $q->whereHas('product.translations', function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->select('id', 'product_id', 'locale', 'title');
                });

            });
    }

    public function scopeWithProductTranslations($query, $array)
    {

        if (!data_get($array, 'lang')) {
            return $query;
        }

        return $query->with([
            'product:id,uuid,unit_id',
            'product.unit:id,active,position',
            'product.unit.translation',
            'product.translation:id,product_id,locale,title',
        ])->whereHas('product.translations', function ($q) use ($array) {
            $q->select(['id', 'product_id', 'locale', 'title'])
                ->where('locale', data_get($array, 'lang'));
        });
    }



}
