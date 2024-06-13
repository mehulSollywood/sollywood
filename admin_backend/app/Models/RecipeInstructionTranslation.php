<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RecipeInstructionTranslation
 *
 * @property int $id
 * @property int $instruction_id
 * @property string $title
 * @property string $locale
 * @method static Builder|RecipeInstructionTranslation newModelQuery()
 * @method static Builder|RecipeInstructionTranslation newQuery()
 * @method static Builder|RecipeInstructionTranslation query()
 * @method static Builder|RecipeInstructionTranslation whereId($value)
 * @method static Builder|RecipeInstructionTranslation whereInstructionId($value)
 * @method static Builder|RecipeInstructionTranslation whereLocale($value)
 * @method static Builder|RecipeInstructionTranslation whereTitle($value)
 * @mixin Eloquent
 */
class RecipeInstructionTranslation extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'locale'];
    public $timestamps = false;

}
