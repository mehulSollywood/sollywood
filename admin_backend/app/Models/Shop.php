<?php

namespace App\Models;

use App\Helpers\Utility;
use App\Traits\Loadable;
use App\Traits\SetCurrency;
use Database\Factories\ShopFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property float $tax
 * @property int|null $delivery_range
 * @property float $percentage
 * @property array|null $location
 * @property string|null $phone
 * @property int|null $show_type
 * @property int $open
 * @property int $visibility
 * @property string|null $background_img
 * @property string|null $logo_img
 * @property float $min_amount
 * @property string $status
 * @property string $slug
 * @property string $type_of_business
 * @property string $category
 * @property integer $commission
 * @property string $adhar
 * @property string $pan
 * @property string $business_res_certi
 * @property string $gst
 * @property integer $price
 * @property integer $price_per_km
 * @property integer $rate_price
 * @property integer $rate_price_per_km
 * @property string|null $status_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $group_id
 * @property array|null $delivery_time
 * @property-read Collection|Brand[] $brands
 * @property-read int|null $brands_count
 * @property-read Collection|Category[] $categories
 * @property-read int|null $categories_count
 * @property-read Collection|Delivery[] $deliveries
 * @property-read int|null $deliveries_count
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read mixed $working_status
 * @property-read Group|null $group
 * @property-read Collection|Invitation[] $invitations
 * @property-read int|null $invitations_count
 * @property-read Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Collection|Product[] $products
 * @property-read int|null $products_count
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read User $seller
 * @property-read Collection|ShopPayment[] $shopPayments
 * @property-read int|null $shop_payments_count
 * @property-read ShopSubscription|null $subscription
 * @property-read ShopTranslation|null $translation
 * @property-read Collection|ShopTranslation[] $translations
 * @property-read DeliveryZone|null $deliveryZone
 * @property-read int|null $translations_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @property mixed $reviews_avg_rating
 * @method static ShopFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Shop newQuery()
 * @method static Builder|Shop onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Shop query()
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereBackgroundImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereCloseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereDeliveryRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereLogoImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereMinAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereOpenTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereShowType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereStatusNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Shop whereVisibility($value)
 * @method static Builder|Shop withTrashed()
 * @method static Builder|Shop withoutTrashed()
 * @method whenLoaded(string $string)
 * @mixin Eloquent
 * @property-read Collection|ShopProduct[] $shopProducts
 * @property-read int|null $shop_products_count
 */
class Shop extends Model
{
    use HasFactory, SoftDeletes, Loadable, SetCurrency;

    protected $guarded = [];

    const STATUS = [
        'new',
        'edited',
        'approved',
        'rejected',
        'inactive'
    ];

    protected $casts = [
        'location' => 'array',
        'delivery_time' => 'array',
    ];


    const DELIVERY_TIME_MINUTE = 'minute';
    const DELIVERY_TIME_HOUR = 'hour';
    const DELIVERY_TIME_DAY = 'day';
    const DELIVERY_TIME_MONTH = 'month';


    public function getRatePriceAttribute(): ?float
    {

        if (request()->is('api/v1/dashboard/user/*')) {
            return round($this->price * $this->currency(), 2);
        }

        return $this->price;
    }

    public function getRatePricePerKmAttribute(): ?float
    {
        if (request()->is('api/v1/dashboard/user/*')) {
            return round($this->price_per_km * $this->currency(), 2);
        }

        return $this->price_per_km;
    }

    public function workingDays(): HasMany
    {
        return $this->hasMany(ShopWorkingDay::class);
    }

    public function closedDates(): HasMany
    {
        return $this->hasMany(ShopClosedDate::class);
    }

    public function deliveryZone(): HasOne
    {
        return $this->hasOne(DeliveryZone::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShopTranslation::class);
    }

    public function shopPayments(): HasMany
    {
        return $this->hasMany(ShopPayment::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ShopTranslation::class)->where('locale', app()->getLocale());
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'shop_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Invitation::class,
            'shop_id', 'id', 'id', 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Order::class,
            'shop_id', 'reviewable_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(ShopSubscription::class, 'shop_id')
            ->whereDate('expired_at', '>=', today())->where(['active' => 1])->orderByDesc('id');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'shop_brands', 'shop_id', 'brand_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'shop_categories', 'shop_id', 'category_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ShopTag::class, 'assign_shop_tags', 'shop_id', 'shop_tag_id');
    }

    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['user_id']), function ($q) use ($array) {
                $q->where('user_id', $array['user_id']);
            })
            ->when(isset($array['group_id']), function ($q) use ($array) {
                $q->where('group_id', $array['group_id']);
            })
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            })
            ->when(isset($array['visibility']), function ($q) use ($array) {
                $q->where('visibility', $array['visibility']);
            })
            ->when(isset($array['always_open']), function ($q) use ($array) {
                $q->whereHas('closedDates', function ($q) {
                    $q->where('date', '!=', today());
                });
            })
            ->when(isset($array['open']), function ($q) use ($array) {
                $q->where('open', 1);
            })
            ->when(data_get($array, 'address'), function ($query) use ($array) {
                $orderByIds = [];
                $query->whereHas('deliveryZone', function ($q) use ($array, &$orderByIds) {

                    $orders = [];
                    $shopIds = DeliveryZone::list()->map(function (DeliveryZone $deliveryZone) use ($array, &$orders) {

                        if (!$deliveryZone->shop_id) {
                            return null;
                        }

                        $shop = $deliveryZone->shop;

                        $location = data_get($deliveryZone->shop, 'location', []);

                        $km = (new Utility)->getDistance($location, data_get($array, 'address', []));
                        $rate = data_get($array, 'currency.rate', 1);

                        $orders[$deliveryZone->shop_id] = (new Utility)->getPriceByDistance($km, $shop, $rate);

                        if (
                            Utility::pointInPolygon(data_get($array, 'address'), $deliveryZone->address)
                            && $orders[$deliveryZone->shop_id] > 0
                        ) {
                            return $deliveryZone->shop_id;
                        }

                        unset($orders[$deliveryZone->shop_id]);

                        return null;
                    })
                        ->reject(fn($data) => empty($data))
                        ->toArray();
                    // Что бы отсортировать по наименьшей цене нужны значения,
                    // далее для фильтра берём только ключи(shop_id) array_keys($orders)
                    asort($orders);

                    $orderByIds = implode(', ', array_keys($orders));

                    $q->whereIn('shop_id', $shopIds);

                })->when($orderByIds, fn($builder) => $builder->orderByRaw(DB::raw("FIELD(shops.id, $orderByIds) ASC")));

            })
            ->when(data_get($array, 'search'), function ($q, $search) {
                $q->whereHas('translations', function ($q) use ($search) {
                    $q->where('title', 'LIKE', '%' . $search . '%')
                        ->select('id', 'shop_id', 'locale', 'title');
                });

            })
            ->when(isset($array['delivery']), function ($q) use ($array) {
                $q->whereHas('deliveries', function ($q) use ($array) {
                    if ($array['delivery'] == 'pickup') {
                        $q->where('type', $array['delivery']);
                    } else {
                        $q->where('type', '!=', 'pickup');
                    }
                });
            });
    }
}
