<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BonusShop
 *
 * @property int $id
 * @property int|null $shop_id
 * @property int|null $bonus_product_id
 * @property int $bonus_quantity
 * @property float $order_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $expired_at
 * @property int $status
 * @property-read Shop|null $shop
 * @property-read ShopProduct|null $shopProduct
 * @method static Builder|BonusShop active()
 * @method static Builder|BonusShop newModelQuery()
 * @method static Builder|BonusShop newQuery()
 * @method static Builder|BonusShop query()
 * @method static Builder|BonusShop whereBonusProductId($value)
 * @method static Builder|BonusShop whereBonusQuantity($value)
 * @method static Builder|BonusShop whereCreatedAt($value)
 * @method static Builder|BonusShop whereExpiredAt($value)
 * @method static Builder|BonusShop whereId($value)
 * @method static Builder|BonusShop whereOrderAmount($value)
 * @method static Builder|BonusShop whereShopId($value)
 * @method static Builder|BonusShop whereStatus($value)
 * @method static Builder|BonusShop whereUpdatedAt($value)
 * @mixin Eloquent
 */
class BonusShop extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id','bonus_product_id','bonus_quantity','order_amount','status','expired_at'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function shopProduct(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'bonus_product_id','id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true)->whereDate('expired_at', '>=', now());
    }

}
