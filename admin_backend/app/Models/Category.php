<?php

namespace App\Models;

use App\Traits\Loadable;
use Database\Factories\CategoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $keywords
 * @property int|null $referralPercentage
 * @property int|null $gstPercentage
 * @property string|null $slug
 * @property int|null $parent_id
 * @property int $type
 * @property string|null $img
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Category[] $children
 * @property-read int|null $children_count
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read Category|null $parent
 * @property-read Collection|Product[] $products
 * @property-read int|null $products_count
 * @property-read ShopCategory|null $shopCategory
 * @property-read Collection|ShopProduct[] $shopProduct
 * @property-read Collection|ShopProduct[] $shopProductWithTrashed
 * @property-read int|null $shop_product_count
 * @property-read CategoryTranslation|null $translation
 * @property-read Collection|CategoryTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static CategoryFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Category filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static Builder|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUuid($value)
 * @method static Builder|Category withTrashed()
 * @method static Builder|Category withoutTrashed()
 * @mixin Eloquent
 */
class Category extends Model
{
    use HasFactory, Loadable, SoftDeletes;

    protected $guarded = [];

    const TYPES = [
        'main' => 1,
        'blog' => 2,
        'brand' => 3
    ];

    public function getTypeAttribute($value)
    {
        foreach (self::TYPES as $index => $type) {
            if ($type === $value) {
                return $index;
            }
        }
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(CategoryTranslation::class)->where('locale', app()->getLocale());
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenWithTrashed(): HasMany
    {
        return $this->children()->withTrashed();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productsWithTrashed(): HasMany
    {
        return $this->products()->withTrashed();
    }

    public function shopCategory(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'id', 'category_id');
    }

    public function shopProduct(): HasManyThrough
    {
        return $this->hasManyThrough(ShopProduct::class, Product::class, 'category_id', 'product_id')->withCount('reviews');
    }

    public function shopProductWithTrashed(): HasManyThrough
    {
        return $this->hasManyThrough(ShopProduct::class, Product::class, 'category_id', 'product_id')->withTrashed();
    }
    /* Filter Scope */
    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->whereHas('translations', function ($q) use ($array) {
                    $q->where('title', 'LIKE', '%' . $array['search'] . '%')
                        ->select('id', 'category_id', 'locale', 'title');
                });
            })
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->whereActive($array['active']);
            });
    }
}
