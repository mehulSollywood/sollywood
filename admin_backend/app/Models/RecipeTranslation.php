<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RecipeTranslation
 *
 * @property int $id
 * @property int $recipe_id
 * @property string $title
 * @property string $locale
 * @method static Builder|RecipeTranslation newModelQuery()
 * @method static Builder|RecipeTranslation newQuery()
 * @method static Builder|RecipeTranslation query()
 * @method static Builder|RecipeTranslation whereId($value)
 * @method static Builder|RecipeTranslation whereLocale($value)
 * @method static Builder|RecipeTranslation whereRecipeId($value)
 * @method static Builder|RecipeTranslation whereTitle($value)
 * @mixin Eloquent
 */
class RecipeTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'instruction' => 'array',
        'ingredient' => 'array',
        'nutrition' => 'array'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['recipe_id','title','instruction','locale','ingredient','nutrition'];

}
