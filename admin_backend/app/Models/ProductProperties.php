<?php

namespace App\Models;

use Database\Factories\ProductPropertiesFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductProperties
 *
 * @property int $id
 * @property int $product_id
 * @property string $locale
 * @property string $key
 * @property string|null $value
 * @method static ProductPropertiesFactory factory(...$parameters)
 * @method static Builder|ProductProperties newModelQuery()
 * @method static Builder|ProductProperties newQuery()
 * @method static Builder|ProductProperties query()
 * @method static Builder|ProductProperties whereId($value)
 * @method static Builder|ProductProperties whereKey($value)
 * @method static Builder|ProductProperties whereLocale($value)
 * @method static Builder|ProductProperties whereProductId($value)
 * @method static Builder|ProductProperties whereValue($value)
 * @mixin Eloquent
 */
class ProductProperties extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'key', 'value'];
    public $timestamps = false;


}
