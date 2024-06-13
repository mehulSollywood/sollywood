<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GroupTranslation
 *
 * @property int $id
 * @property int|null $group_id
 * @property string $title
 * @property string $locale
 * @property string|null $created_at
 * @property string|null $updated_at
 * @method static Builder|GroupTranslation newModelQuery()
 * @method static Builder|GroupTranslation newQuery()
 * @method static Builder|GroupTranslation query()
 * @method static Builder|GroupTranslation whereCreatedAt($value)
 * @method static Builder|GroupTranslation whereGroupId($value)
 * @method static Builder|GroupTranslation whereId($value)
 * @method static Builder|GroupTranslation whereLocale($value)
 * @method static Builder|GroupTranslation whereTitle($value)
 * @method static Builder|GroupTranslation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class GroupTranslation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['title','locale'];
}
