<?php

namespace App\Models;

use App\Traits\Loadable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\ShopTag
 *
 * @property int $id
 * @property int $img
 * @property Collection|Gallery[] $galleries
 * @property int $galleries_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read BranchTranslation|null $translation
 * @property-read Collection|BranchTranslation[] $translations
 * @property-read int|null $translations_count
 * @property-read Collection|BranchTranslation[] $assignShopTags
 * @property-read int|null $assign_shop_tags_count
 * @method static Builder|ShopTag newModelQuery()
 * @method static Builder|ShopTag newQuery()
 * @method static Builder|ShopTag query()
 * @method static Builder|ShopTag whereCreatedAt($value)
 * @method static Builder|ShopTag whereUpdatedAt($value)
 * @method static Builder|ShopTag whereDeletedAt($value)
 * @method static Builder|ShopTag whereId($value)
 * @method static Builder|ShopTag whereShopId($value)
 * @mixin Eloquent
 */
class ShopTag extends Model
{
    use HasFactory, Loadable;

    protected $guarded = ['id'];

    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(ShopTagTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ShopTagTranslation::class);
    }

    public function assignShopTags(): HasMany
    {
        return $this->hasMany(AssignShopTag::class);
    }
}
