<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\CartDetail
 *
 * @property int $id
 * @property int $shop_product_id
 * @property int $user_cart_id
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float|null $price
 * @property int $bonus
 * @property-read ShopProduct|null $shopProduct
 * @property-read BonusProduct|null $productBonus
 * @property-read UserCart $userCart
 * @method static Builder|CartDetail newModelQuery()
 * @method static Builder|CartDetail newQuery()
 * @method static Builder|CartDetail query()
 * @method static Builder|CartDetail whereBonus($value)
 * @method static Builder|CartDetail whereCreatedAt($value)
 * @method static Builder|CartDetail whereId($value)
 * @method static Builder|CartDetail wherePrice($value)
 * @method static Builder|CartDetail whereQuantity($value)
 * @method static Builder|CartDetail whereShopProductId($value)
 * @method static Builder|CartDetail whereUpdatedAt($value)
 * @method static Builder|CartDetail whereUserCartId($value)
 * @mixin Eloquent
 */
class CartDetail extends Model
{
    use HasFactory,SetCurrency;
    protected $fillable = ['shop_product_id','price','quantity','discount','user_cart_id','bonus'];

    public function shopProduct(): HasOne
    {
        return $this->hasOne(ShopProduct::class,'id','shop_product_id');
    }

    public function productBonus(): BelongsTo
    {
        return $this->belongsTo(BonusProduct::class,'shop_product_id','bonus_product_id');
    }

    public function userCart(): BelongsTo
    {
        return $this->belongsTo(UserCart::class);
    }

    public function getpriceAttribute($value)
    {
//        if (request()->currency)
//        {
//            $currency = Currency::where('id',request()->currency)->first();
//            return $value * $currency->rate;
//        }
        return $value * $this->currency();
    }
}
