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
 * App\Models\Branch
 *
 * @property int $id
 * @property int $shop_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float|null $longitude
 * @property float|null $latitude
 * @property-read BranchTranslation|null $translation
 * @property-read Collection|BranchTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Branch newModelQuery()
 * @method static Builder|Branch newQuery()
 * @method static Builder|Branch query()
 * @method static Builder|Branch whereCreatedAt($value)
 * @method static Builder|Branch whereId($value)
 * @method static Builder|Branch whereLatitude($value)
 * @method static Builder|Branch whereLongitude($value)
 * @method static Builder|Branch whereShopId($value)
 * @method static Builder|Branch whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['shop_id','latitude','longitude'];

    public function translations(): HasMany
    {
        return $this->hasMany(BranchTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(BranchTranslation::class)->where('locale',app()->getLocale());
    }
}
