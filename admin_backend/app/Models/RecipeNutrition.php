<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\RecipeNutrition
 *
 * @property int $id
 * @property int $recipe_id
 * @property string|null $weight
 * @property string|null $percentage
 * @property-read RecipeNutritionTranslation|null $translation
 * @property-read Collection|RecipeNutritionTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|RecipeNutrition newModelQuery()
 * @method static Builder|RecipeNutrition newQuery()
 * @method static Builder|RecipeNutrition query()
 * @method static Builder|RecipeNutrition whereId($value)
 * @method static Builder|RecipeNutrition wherePercentage($value)
 * @method static Builder|RecipeNutrition whereRecipeId($value)
 * @method static Builder|RecipeNutrition whereWeight($value)
 * @mixin Eloquent
 */
class RecipeNutrition extends Model
{
    use HasFactory;

    protected $fillable = ['weight', 'percentage', 'recipe_id'];
    public $timestamps = false;
    public function translations(): HasMany
    {
        return $this->hasMany(RecipeNutritionTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(RecipeNutritionTranslation::class)->where('locale',app()->getLocale());
    }
}
