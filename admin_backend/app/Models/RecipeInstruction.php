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
 * App\Models\RecipeInstruction
 *
 * @property int $id
 * @property int $recipe_id
 * @property-read RecipeInstructionTranslation|null $translation
 * @property-read Collection|RecipeInstructionTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|RecipeInstruction newModelQuery()
 * @method static Builder|RecipeInstruction newQuery()
 * @method static Builder|RecipeInstruction query()
 * @method static Builder|RecipeInstruction whereId($value)
 * @method static Builder|RecipeInstruction whereRecipeId($value)
 * @mixin Eloquent
 */
class RecipeInstruction extends Model
{
    use HasFactory;
    protected $fillable = ['recipe_id'];
    public $timestamps = false;

    public function translations(): HasMany
    {
        return $this->hasMany(RecipeInstructionTranslation::class, 'instruction_id', 'id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(RecipeInstructionTranslation::class,'instruction_id', 'id')->where('locale',app()->getLocale());
    }
}
