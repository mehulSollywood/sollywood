<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use App\Traits\SetCurrency;
use Database\Factories\ProductFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $uuid
 * @property int $category_id
 * @property int $brand_id
 * @property int|null $unit_id
 * @property boolean|null $gift
 * @property string|null $keywords
 * @property string|null $slug
 * @property string|null $img
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $qr_code
 * @property-read Brand $brand
 * @property-read Category $category
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read int|null $product_sales_count
 * @property-read Collection|ProductProperties[] $properties
 * @property-read int|null $properties_count
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read ShopProduct|null $shopProduct
 * @property-read ShopProduct|null $shopProducts
 * @property-read ProductTranslation|null $translation
 * @property-read Collection|ProductTranslation[] $translations
 * @property-read int|null $translations_count
 * @property-read Unit|null $unit
 * @method static ProductFactory factory(...$parameters)
 * @method static Builder|Product filter($array)
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product onlyTrashed()
 * @method static Builder|Product query()
 * @method static Builder|Product whereBrandId($value)
 * @method static Builder|Product whereCategoryId($value)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereDeletedAt($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereImg($value)
 * @method static Builder|Product whereKeywords($value)
 * @method static Builder|Product whereQrCode($value)
 * @method static Builder|Product whereUnitId($value)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product whereUuid($value)
 * @method static Builder|Product withTrashed()
 * @method static Builder|Product withoutTrashed()
 * @mixin Eloquent
 */
class Product extends Model
{
    use HasFactory, SoftDeletes, Loadable, Reviewable, SetCurrency;

    protected $fillable = [
        'category_id',
        'brand_id',
        'unit_id',
        'keywords',
        'qr_code',
        'img',
        'gift',
        'slug',
    ];

    protected bool $softDelete = true;

    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class)->where('locale', app()->getLocale());
    }

    // Product Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    // Product Brand
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // Product Properties
    public function properties(): HasMany
    {
        return $this->hasMany(ProductProperties::class);
    }


    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

//    public function discount()
//    {
//        return $this->belongsToMany(Discount::class, ProductDiscount::class);
//    }

    public function shopProduct(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'id', 'product_id');
    }

    public function shopProductWithTrashed(): BelongsTo
    {
        return $this->shopProduct()->withTrashed();
    }



    public function shopProducts(): HasMany
    {
        return $this->hasMany(ShopProduct::class);
    }

    public function shopProductsWithTrashed(): HasMany
    {
        return $this->shopProducts()->withTrashed();
    }



    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['category_id']), function ($q) use ($array) {
                $q->where('category_id', $array['category_id']);
            })
            ->when(isset($array['qr_code']), function ($q) use ($array) {
                $q->where('qr_code', 'LIKE', '%' . $array['qr_code'] . '%');
            })->when(isset($array['search']), function ($query) use ($array) {
                $query->whereHas('translations', function ($q) use ($array) {
                    $q->where('title', 'LIKE', '%' . $array['search'] . '%');
                });
            })
            ->when(isset($array['brand_id']), function ($q) use ($array) {
                $q->where('brand_id', $array['brand_id']);
            });
    }
}
