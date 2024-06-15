<?php

namespace App\Models;

use Eloquent;
use App\Traits\Loadable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ShopCategory
 *
 * @property int $id
 * @property int $firstname
 * @method static Builder|ShopCategory newModelQuery()
 * @method static Builder|ShopCategory newQuery()
 * @method static Builder|ShopCategory query()
 * @method static Builder|ShopCategory whereCategoryId($value)
 * @method static Builder|ShopCategory whereId($value)
 * @method static Builder|ShopCategory whereShopId($value)
 * @mixin Eloquent
 */
class ShopList extends Model
{
    use HasFactory, Loadable, Reviewable;

    protected $fillable = [
        'id',
        'user_id',
    ];
    protected $table = 'shops';
}
