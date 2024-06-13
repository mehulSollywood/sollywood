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
 * App\Models\Delivery
 *
 * @property int $id
 * @property int|null $shop_id
 * @property string $type
 * @property float $price
 * @property array|null $times
 * @property string|null $note
 * @property int $default
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DeliveryTranslation|null $translation
 * @property-read Collection|DeliveryTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static Builder|Delivery filter()
 * @method static Builder|Delivery newModelQuery()
 * @method static Builder|Delivery newQuery()
 * @method static Builder|Delivery query()
 * @method static Builder|Delivery whereActive($value)
 * @method static Builder|Delivery whereCreatedAt($value)
 * @method static Builder|Delivery whereDefault($value)
 * @method static Builder|Delivery whereId($value)
 * @method static Builder|Delivery whereNote($value)
 * @method static Builder|Delivery wherePrice($value)
 * @method static Builder|Delivery whereShopId($value)
 * @method static Builder|Delivery whereTimes($value)
 * @method static Builder|Delivery whereType($value)
 * @method static Builder|Delivery whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Delivery extends Model
{
    use HasFactory;
    protected $fillable = ['shop_id', 'price', 'times', 'note', 'active', 'type'];
    protected $casts = [
      'times' => 'array'
    ];

    const TYPE_PICKUP   = 'pickup';
    const TYPE_FREE     = 'free';
    const TYPE_STANDARD = 'standard';
    const TYPE_EXPRESS  = 'express';

    const TYPES = [
        self::TYPE_PICKUP,
        self::TYPE_FREE,
        self::TYPE_STANDARD,
        self::TYPE_EXPRESS,
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(DeliveryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(DeliveryTranslation::class)->where('locale',app()->getLocale());
    }

    public function getPriceAttribute($value)
    {
        $currency = isset(request()->currency_id)
            ? Currency::currenciesList()->where('id', request()->currency_id)->first()
            : Currency::currenciesList()->where('default', 1)->first();

        return round($value * $currency?->rate, 2);
    }

    public function scopeFilter()
    {

    }
}
