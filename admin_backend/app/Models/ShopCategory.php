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
 * App\Models\ShopCategory
 *
 * @property int $id
 * @property int $shop_id
 * @property int $category_id
 * @property-read Collection|Category[] $categories
 * @property-read int|null $categories_count
 * @method static Builder|ShopCategory newModelQuery()
 * @method static Builder|ShopCategory newQuery()
 * @method static Builder|ShopCategory query()
 * @method static Builder|ShopCategory whereCategoryId($value)
 * @method static Builder|ShopCategory whereId($value)
 * @method static Builder|ShopCategory whereShopId($value)
 * @mixin Eloquent
 * @property-read Category $category
 */
class ShopCategory extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['shop_id', 'category_id'];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class,'id', 'category_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
