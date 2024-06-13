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
 * App\Models\Group
 *
 * @property int $id
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read GroupTranslation|null $translation
 * @property-read Collection|GroupTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group query()
 * @method static Builder|Group whereCreatedAt($value)
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereStatus($value)
 * @method static Builder|Group whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|Shop[] $shops
 * @property-read int|null $shops_count
 */
class Group extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['status'];

    // Translations

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(GroupTranslation::class);
    }

    /**
     * @return HasOne
     */
    public function translation(): HasOne
    {
        return $this->hasOne(GroupTranslation::class)->where('locale',app()->getLocale());
    }

    /**
     * @return HasMany
     */
    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }
}
