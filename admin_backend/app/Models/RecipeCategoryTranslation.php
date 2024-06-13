<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\RecipeCategoryTranslation
 *
 * @property int $id
 * @property int $recipe_category_id
 * @property string $title
 * @property string|null $description
 * @property string $locale
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|RecipeCategoryTranslation newQuery()
 * @method static Builder|RecipeCategoryTranslation query()
 * @method static Builder|RecipeCategoryTranslation whereCreatedAt($value)
 * @method static Builder|RecipeCategoryTranslation whereDescription($value)
 * @method static Builder|RecipeCategoryTranslation whereId($value)
 * @method static Builder|RecipeCategoryTranslation whereLocale($value)
 * @method static Builder|RecipeCategoryTranslation whereRecipeCategoryId($value)
 * @method static Builder|RecipeCategoryTranslation whereTitle($value)
 * @method static Builder|RecipeCategoryTranslation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class RecipeCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','locale'];

}
