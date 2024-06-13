<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Cart
 *
 * @property int $id
 * @property int $shop_id
 * @property int $owner_id
 * @property int $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float|null $total_price
 * @property int $status
 * @property-read User|null $user
 * @property-read UserCart|null $userCart
 * @property-read Collection|UserCart[] $userCarts
 * @property-read int|null $user_carts_count
 * @method static Builder|Cart newModelQuery()
 * @method static Builder|Cart newQuery()
 * @method static Builder|Cart query()
 * @method static Builder|Cart whereCreatedAt($value)
 * @method static Builder|Cart whereId($value)
 * @method static Builder|Cart whereOwnerId($value)
 * @method static Builder|Cart whereShopId($value)
 * @method static Builder|Cart whereStatus($value)
 * @method static Builder|Cart whereTotalPrice($value)
 * @method static Builder|Cart whereUpdatedAt($value)
 * @mixin Eloquent
 * @property int $together
 * @method static Builder|Cart whereTogether($value)
 */
class Cart extends Model
{
    use HasFactory, SetCurrency, SoftDeletes;

    protected $fillable = ['shop_id', 'uuid', 'owner_id', 'total_price', 'status', 'together','quantity'];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'owner_id');
    }

    public function userCarts(): HasMany
    {
        return $this->hasMany(UserCart::class);
    }

    public function userCart(): HasOne
    {
        return $this->hasOne(UserCart::class);
    }

    public function getTotalPriceAttribute($value)
    {
        if (request()->currency) {
            $currency = Currency::where('id', request()->currency)->first();
            if ($currency){
                return $value * $currency->rate ?? 1;
            }
        }
        return $value * $this->currency();
    }
}
