<?php

namespace App\Models;

use App\Traits\Loadable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\Recipe
 *
 * @property int $id
 * @property int|null $shop_id
 * @property int|null $user_id
 * @property string|null $image
 * @property int $status
 * @property int|null $active_time
 * @property int|null $total_time
 * @property int|null $calories
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $recipe_category_id
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read RecipeInstruction|null $instruction
 * @property-read Collection|RecipeInstruction[] $instructions
 * @property-read int|null $instructions_count
 * @property-read RecipeNutrition|null $nutrition
 * @property-read Collection|RecipeNutrition[] $nutritions
 * @property-read int|null $nutritions_count
 * @property-read RecipeProduct $product
 * @property-read Collection|RecipeProduct[] $products
 * @property-read int|null $products_count
 * @property-read RecipeCategory|null $recipeCategory
 * @property-read Collection|Product[] $recipeProduct
 * @property-read int|null $recipe_product_count
 * @property-read RecipeTranslation|null $translation
 * @property-read Collection|RecipeTranslation[] $translations
 * @property-read int|null $translations_count
 * @property-read User|null $user
 * @method static Builder|Recipe newModelQuery()
 * @method static Builder|Recipe newQuery()
 * @method static Builder|Recipe query()
 * @method static Builder|Recipe whereActiveTime($value)
 * @method static Builder|Recipe whereCalories($value)
 * @method static Builder|Recipe whereCreatedAt($value)
 * @method static Builder|Recipe whereId($value)
 * @method static Builder|Recipe whereImage($value)
 * @method static Builder|Recipe whereRecipeCategoryId($value)
 * @method static Builder|Recipe whereShopId($value)
 * @method static Builder|Recipe whereStatus($value)
 * @method static Builder|Recipe whereTotalTime($value)
 * @method static Builder|Recipe whereUpdatedAt($value)
 * @method static Builder|Recipe whereUserId($value)
 * @mixin Eloquent
 */
class Recipe extends Model
{
    use HasFactory, Loadable,SoftDeletes;

    protected $fillable = ['shop_id','image','active_time','total_time','calories','recipe_category_id','user_id','status'];

    public function translations() : HasMany
    {
        return $this->hasMany(RecipeTranslation::class);
    }

    public function translation() : HasOne
    {
        return $this->hasOne(RecipeTranslation::class);
    }

    public function nutritions() : HasMany
    {
        return $this->hasMany(RecipeNutrition::class);
    }

    public function nutrition() : HasOne
    {
        return $this->hasOne(RecipeNutrition::class);
    }

    public function instructions() : HasMany
    {
        return $this->hasMany(RecipeInstruction::class);
    }

    public function instruction() : HasOne
    {
        return $this->hasOne(RecipeInstruction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(RecipeProduct::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(RecipeProduct::class);
    }

    public function recipeProduct(): BelongsToMany
    {
        return $this->belongsToMany(Product::class,'recipe_products','recipe_id','product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipeCategory(): HasOne
    {
        return $this->hasOne(RecipeCategory::class,'id','recipe_category_id');
    }
}
