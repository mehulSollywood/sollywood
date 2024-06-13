<?php

namespace App\Models;

use App\Traits\Notification;
use App\Traits\Payable;
use App\Traits\Reviewable;
use Database\Factories\OrderDetailFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request;

/**
 * App\Models\OrderDetail
 *
 * @property int $id
 * @property int $order_id
 * @property float $origin_price
 * @property float $total_price
 * @property float $tax
 * @property float $discount
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $shop_product_id
 * @property int $bonus
 * @property-read Order $order
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read ShopProduct|null $shopProduct
 * @property-read Collection|Transaction[] $transactions
 * @property-read int|null $transactions_count
 * @method static OrderDetailFactory factory(...$parameters)
 * @method static Builder|OrderDetail filter($array)
 * @method static Builder|OrderDetail newModelQuery()
 * @method static Builder|OrderDetail newQuery()
 * @method static Builder|OrderDetail query()
 * @method static Builder|OrderDetail whereBonus($value)
 * @method static Builder|OrderDetail whereCreatedAt($value)
 * @method static Builder|OrderDetail whereDiscount($value)
 * @method static Builder|OrderDetail whereId($value)
 * @method static Builder|OrderDetail whereOrderId($value)
 * @method static Builder|OrderDetail whereOriginPrice($value)
 * @method static Builder|OrderDetail whereQuantity($value)
 * @method static Builder|OrderDetail whereShopProductId($value)
 * @method static Builder|OrderDetail whereTax($value)
 * @method static Builder|OrderDetail whereTotalPrice($value)
 * @method static Builder|OrderDetail whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderDetail extends Model
{
    use HasFactory, Payable, Notification, Reviewable;
    protected $guarded = [];

    protected $fillable = ['order_id','origin_price','total_price','tax','discount','quantity','shop_product_id','bonus'];


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderWithTrashed(): BelongsTo
    {
        return $this->order()->withTrashed();
    }

    public function shopProduct(): hasOne
    {
        return $this->hasOne(ShopProduct::class, 'id','shop_product_id');
    }

    public function shopProductsWithTrashed()
    {
        return $this->belongsTo(ShopProduct::class,'shop_product_id','id')->withTrashed();
    }


    public function getOriginPriceAttribute($value)
    {
        if (request()->is('api/v1/dashboard/user/orders/*') && Request::isMethod('get')){
            return $value;
        }
        if (request()->is('api/v1/dashboard/user/*') && Request::isMethod('get')){
            return round($value * $this->order->rate, 2);
        } else {
            return $value;
        }
    }

    public function getTotalPriceAttribute($value)
    {
        if (request()->is('api/v1/dashboard/user/orders/*') && Request::isMethod('get')){
            return $value;
        }
        if (request()->is('api/v1/dashboard/user/*') && Request::isMethod('get')){
            return round($value * $this->order->rate, 2);
        } else {
            return $value;
        }
    }

    public function getDiscountAttribute($value)
    {
        if (request()->is('api/v1/dashboard/user/orders/*') && Request::isMethod('get')){
            return $value;
        }
        if (request()->is('api/v1/dashboard/user/*') && Request::isMethod('get')){
            return round($value * $this->order->rate, 2);
        } else {
            return $value;
        }
    }

    public function getTaxAttribute($value)
    {
        if (request()->is('api/v1/dashboard/user/orders/*') && Request::isMethod('get')){
            return $value;
        }
        if (request()->is('api/v1/dashboard/user/*')){
            return round($value * $this->order->rate, 2);
        } else {
            return $value;
        }
    }


    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['status']), function ($q) use ($array) {
                $q->where('status', $array['status']);
            });
    }
}
