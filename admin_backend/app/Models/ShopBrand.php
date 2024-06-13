<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ShopBrand
 *
 * @property int $id
 * @property int $shop_id
 * @property int $brand_id
 * @property-read Brand|null $brand
 * @property-read Collection|Brand[] $brands
 * @property-read int|null $brands_count
 * @method static Builder|ShopBrand newModelQuery()
 * @method static Builder|ShopBrand newQuery()
 * @method static Builder|ShopBrand query()
 * @method static Builder|ShopBrand whereBrandId($value)
 * @method static Builder|ShopBrand whereId($value)
 * @method static Builder|ShopBrand whereShopId($value)
 * @mixin Eloquent
 */
class ShopBrand extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'shop_brands';
    public $fillable = ['shop_id', 'brand_id'];

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class,'id', 'brand_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

}
