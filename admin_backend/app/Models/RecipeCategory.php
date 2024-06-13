<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\RecipeCategory
 *
 * @property int $id
 * @property int $status
 * @property int|null $parent_id
 * @property string|null $image
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|RecipeCategory[] $child
 * @property-read int|null $child_count
 * @property-read Recipe|null $recipe
 * @property-read Collection|Recipe[] $recipes
 * @property-read int|null $recipes_count
 * @property-read RecipeCategoryTranslation|null $translation
 * @property-read Collection|RecipeCategoryTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|RecipeCategory newModelQuery()
 * @method static Builder|RecipeCategory newQuery()
 * @method static Builder|RecipeCategory query()
 * @method static Builder|RecipeCategory whereCreatedAt($value)
 * @method static Builder|RecipeCategory whereId($value)
 * @method static Builder|RecipeCategory whereImage($value)
 * @method static Builder|RecipeCategory whereParentId($value)
 * @method static Builder|RecipeCategory whereStatus($value)
 * @method static Builder|RecipeCategory whereUpdatedAt($value)
 * @mixin Eloquent
 */
class RecipeCategory extends Model
{
    use HasFactory;

    protected $fillable = ['status','parent_id','image'];

    public function recipes() : HasMany
    {
        return $this->hasMany(Recipe::class);
    }
    public function recipe() : HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function translations() : HasMany
    {
        return $this->hasMany(RecipeCategoryTranslation::class);
    }

    public function translation() : HasOne
    {
        return $this->hasOne(RecipeCategoryTranslation::class)->where('locale',app()->getLocale());
    }

    public function child(): HasMany
    {
        return $this->hasMany(RecipeCategory::class, 'parent_id', 'id')->withCount('recipes');
    }

}
