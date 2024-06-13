<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property double $price
 * @property int $shop_product_id
 */

class UserGiftCart extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','shop_product_id','price'];

    public function shopProduct(): HasOne
    {
        return $this->hasOne(ShopProduct::class,'id','shop_product_id');
    }
}
