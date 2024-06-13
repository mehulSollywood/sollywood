<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\RecipeProduct
 *
 * @property int $id
 * @property int $recipe_id
 * @property string|null $measurement
 * @property int $product_id
 * @property-read ShopProduct|null $shopProduct
 * @method static Builder|RecipeProduct newModelQuery()
 * @method static Builder|RecipeProduct newQuery()
 * @method static Builder|RecipeProduct query()
 * @method static Builder|RecipeProduct whereId($value)
 * @method static Builder|RecipeProduct whereMeasurement($value)
 * @method static Builder|RecipeProduct whereProductId($value)
 * @method static Builder|RecipeProduct whereRecipeId($value)
 * @mixin Eloquent
 */
class RecipeProduct extends Model
{
    use HasFactory;
    protected $fillable = ['product_id','recipe_id','measurement'];
    public $timestamps = false;

    public function shopProduct(): HasOne
    {
        return $this->hasOne(ShopProduct::class,'id','product_id');
    }
}
