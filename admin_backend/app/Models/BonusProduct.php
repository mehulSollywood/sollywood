<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\BonusProduct
 *
 * @property int $id
 * @property int|null $shop_product_id
 * @property int|null $bonus_product_id
 * @property int $bonus_quantity
 * @property int $shop_product_quantity
 * @property string $expired_at
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShopProduct|null $bonusProduct
 * @property-read ShopProduct|null $shopProduct
 * @method static Builder|BonusProduct newModelQuery()
 * @method static Builder|BonusProduct newQuery()
 * @method static Builder|BonusProduct query()
 * @method static Builder|BonusProduct whereBonusProductId($value)
 * @method static Builder|BonusProduct whereBonusQuantity($value)
 * @method static Builder|BonusProduct whereCreatedAt($value)
 * @method static Builder|BonusProduct whereExpiredAt($value)
 * @method static Builder|BonusProduct whereId($value)
 * @method static Builder|BonusProduct whereShopProductId($value)
 * @method static Builder|BonusProduct whereShopProductQuantity($value)
 * @method static Builder|BonusProduct whereStatus($value)
 * @method static Builder|BonusProduct whereUpdatedAt($value)
 * @mixin Eloquent
 */
class BonusProduct extends Model
{
    use HasFactory;

    protected $fillable = ['shop_product_id','bonus_product_id','bonus_quantity','shop_product_quantity','expired_at','status'];


    public function shopProduct() : BelongsTo
    {
        return $this->belongsTo(ShopProduct::class,'shop_product_id','id');
    }

    public function bonusProduct() : BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }


}
