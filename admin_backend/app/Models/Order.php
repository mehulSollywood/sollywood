<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Notification;
use App\Traits\Payable;
use App\Traits\Reviewable;
use App\Traits\SetCurrency;
use Database\Factories\OrderFactory;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int $branch_id
 * @property float $price
 * @property int $currency_id
 * @property int $rate
 * @property int $phone
 * @property int $name
 * @property string|null $note
 * @property string|null $img
 * @property int $shop_id
 * @property float $tax
 * @property float|null $commission_fee
 * @property int|null $cashback_amount
 * @property double|null $money_back
 * @property string $status
 * @property int|null $delivery_address_id
 * @property int|null $gift_cart_id
 * @property int|null $delivery_type_id
 * @property int|null $gift_user_id
 * @property float $delivery_fee
 * @property int|null $deliveryman
 * @property boolean|false $current
 * @property boolean|false $auto_order
 * @property string|null $delivery_date
 * @property string|null $delivery_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $date
 * @property string|null $deleted_at
 * @property int|null $total_discount
 * @property-read OrderCoupon|null $coupon
 * @property-read Currency|null $currency
 * @property-read UserAddress|null $deliveryAddress
 * @property-read User|null $deliveryMan
 * @property-read Delivery|null $deliveryType
 * @property-read UserGiftCart|null $giftCart
 * @property-read Collection|OrderDetail[] $orderDetails
 * @property-read int|null $order_details_count
 * @property-read Review|null $review
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read Shop $shop
 * @property-read Transaction|null $transaction
 * @property-read Collection|Transaction[] $transactions
 * @property-read int|null $transactions_count
 * @property-read User $user
 * @method static OrderFactory factory(...$parameters)
 * @method static Builder|Order filter($array)
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCommissionFee($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrencyId($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDeliveryAddressId($value)
 * @method static Builder|Order whereDeliveryDate($value)
 * @method static Builder|Order whereDeliveryFee($value)
 * @method static Builder|Order whereDeliveryTime($value)
 * @method static Builder|Order whereDeliveryTypeId($value)
 * @method static Builder|Order whereDeliveryman($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order wherePrice($value)
 * @method static Builder|Order whereRate($value)
 * @method static Builder|Order whereShopId($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereTax($value)
 * @method static Builder|Order whereTotalDiscount($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @mixin Eloquent
 * @property int|null $bonus_shop_id
 * @property int|null $cart_id
 * @property-read BonusShop|null $bonusShop
 * @property mixed $order_details_sum_quantity
 * @method static Builder|Order whereBonusShopId($value)
 * @method static Builder|Order whereCartId($value)
 */
class Order extends Model
{
    use HasFactory, Payable, Notification, Reviewable, SetCurrency, SoftDeletes, Loadable;

    protected $guarded = [];

    protected $fillable = [
        'user_id', 
        'price',
        'currency_id',
        'rate',
        'note',
        'shop_id',
        'tax',
        'commission_fee',
        'status',
        'delivery_address_id',
        'user_addresses',
        'delivery_type_id',
        'delivery_fee',
        'deliveryman',
        'delivery_date',
        'delivery_time',
        'total_discount',
        'bonus_shop_id',
        'branch_id',
        'current',
        'address',
        'location',
        'name',
        'phone',
        'auto_order',
        'auto_order_date',
        'created_at',
        'money_back',
        'img',
        'order_template_id',
        'gift_user_id',
        'gift_cart_id',
    ];

    protected $casts = [
        'location' => 'array',
        'address'  => 'array',
    ];

    const NEW       = 'new';
    const ACCEPTED  = 'accepted';
    const READY     = 'ready';
    const ON_A_WAY  = 'on_a_way';
    const DELIVERED = 'delivered';
    const CANCELED  = 'canceled';

    const STATUS = [
        self::NEW       => self::NEW,
        self::ACCEPTED  => self::ACCEPTED,
        self::READY     => self::READY,
        self::ON_A_WAY  => self::ON_A_WAY,
        self::DELIVERED => self::DELIVERED,
        self::CANCELED  => self::CANCELED,
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class, 'payable_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): MorphOne
    {
        return $this->morphOne(Review::class, 'reviewable');
    }

    public function orderTemplate(): HasOne
    {
        return $this->hasOne(OrderTemplate::class);
    }

    public function giftCart(): BelongsTo
    {
        return $this->belongsTo(UserGiftCart::class,'shop_product_id','gift_cart_id');
    }

//    public function getPriceAttribute($value): float
//    {
//        if (request()->is('api/v1/dashboard/user/*')){
//            return round($value * $this->rate, 2);
//        } else {
//            return $value;
//        }
//    }
//
    public function getDeliveryFeeAttribute($value)
    {
        if (request()->is('api/v1/dashboard/user/*')) {
            return round($value * $this->rate, 2);
        } else {
            return $value;
        }
    }


    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliveryman', 'id');
    }

    public function deliveryType(): BelongsTo
    {
        return $this->belongsTo(Delivery::class, 'delivery_type_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'delivery_address_id');
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(OrderCoupon::class, 'order_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function bonusShop(): HasOne
    {
        return $this->hasOne(BonusShop::class, 'id', 'bonus_shop_id');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function paymentProcess(): HasMany
    {
        return $this->hasMany(PaymentProcess::class);
    }


    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeFilter($query, $filter)
    {
        $orderByStatuses = is_array(data_get($filter, 'statuses')) ?
            array_intersect(self::STATUS, data_get($filter, 'statuses')) :
            [];
        $query
            ->when(data_get($filter, 'isset-deliveryman'), function ($q) use ($filter) {
                $q->whereHas('deliveryMan');
            })
            ->when(data_get($filter, 'search'), function ($q, $search) {

                $q->where(function ($b) use ($search) {
                    $b->where('id', 'LIKE', "%$search%")
                        ->orWhere('user_id', $search)
                        ->orWhereHas('user', fn($q) => $q
                            ->where('firstname', 'LIKE', "%$search%")
                            ->orWhere('lastname', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhere('phone', 'LIKE', "%$search%")
                        )
                        ->orWhere('note', 'LIKE', "%$search%");
                });
            })
            ->when(data_get($filter, 'shop_id'), function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            })
            ->when(data_get($filter, 'user_id'), fn($q, $userId) => $q->where('user_id', $userId))
            ->when(data_get($filter, 'delivery_type'), fn($q) => $q->whereHas('deliveryType', function ($b) use ($filter) {
                if ($filter['delivery_type'] == Delivery::TYPE_PICKUP) {
                    $b->where('type', $filter['delivery_type']);
                } else {
                    $b->where('type', '!=', $filter['delivery_type']);
                }
            }))
            ->when(data_get($filter, 'date_from'), function (Builder $query, $dateFrom) use ($filter) {

                $dateTo = data_get($filter, 'date_to', date('Y-m-d'));

                $query->where([
                    ['created_at', '>', $dateFrom],
                    ['created_at', '<', $dateTo],
                ]);
            })
            ->when(data_get($filter, 'delivery_date_from'), function (Builder $query, $dateFrom) use ($filter) {

                $dateFrom = date('Y-m-d', strtotime($dateFrom . ' -1 day'));

                $dateTo = data_get($filter, 'delivery_date_to', date('Y-m-d'));

                $dateTo = date('Y-m-d', strtotime($dateTo . ' +1 day'));

                $query->where([
                    ['delivery_date', '>=', $dateFrom],
                    ['delivery_date', '<=', $dateTo],
                ]);
            })
            ->when(data_get($filter, 'shop_ids'), function ($q, $shopIds) {
                $q->whereIn('shop_id', is_array($shopIds) ? $shopIds : []);
            })
            ->when(data_get($filter, 'status') && data_get($filter, 'status') !== 'all',
                fn($q) => $q->where('status', data_get($filter, 'status'))
            )
            ->when(data_get($filter, 'status') && data_get($filter, 'status') == 'all',
                fn($q) => $q->whereIn('status', [
                    Order::NEW,
                    Order::CANCELED,
                    Order::DELIVERED,
                    Order::ACCEPTED,
                    Order::READY,
                    Order::ON_A_WAY
                ])
            )
            ->when(data_get($filter, 'deliveryman'), fn(Builder $q, $deliveryman) => $q->whereHas('deliveryMan', function ($q) use ($deliveryman) {
                $q->where('id', $deliveryman);
            })
            )
            ->when(data_get($filter, 'empty-deliveryman'), fn(Builder $q) => $q->where(function ($b) {
                $b->where('deliveryman', '=', null)->orWhere('deliveryman', '=', 0);
            })
            )
            ->when(isset($filter['products']), function ($q) use ($filter) {
                $q->whereHas('orderDetails', function ($q) use ($filter) {
                    $q->whereHas('shopProduct', function ($q) use ($filter) {
                        $q->where('id', $filter['products']);
                    });
                });
            })
            ->when(isset($filter['current']), fn($q) => $q->where('current', $filter['current']))
            ->when(isset($filter['deleted_at']), fn($q) => $q->onlyTrashed())
            ->when(count($orderByStatuses) > 0, fn($q) => $q->whereIn('status', $orderByStatuses))
            ->when(data_get($filter, 'order_statuses'), function ($q) use ($orderByStatuses) {
                $q->orderByRaw(
                    DB::raw("FIELD(status, 'new', 'accepted', 'ready', 'on_a_way',  'delivered', 'canceled') ASC")
                );
            }
            );
    }
}
