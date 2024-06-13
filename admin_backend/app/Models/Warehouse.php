<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Warehouse
 *
 * @property int $id
 * @property string $note
 * @property string $type
 * @property int $quantity
 * @property int|null $user_id
 * @property int|null $shop_product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Warehouse filter($array)
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse whereContent($value)
 * @method static Builder|Warehouse whereCreatedAt($value)
 * @method static Builder|Warehouse whereCreatedBy($value)
 * @method static Builder|Warehouse whereId($value)
 * @method static Builder|Warehouse whereOrderId($value)
 * @method static Builder|Warehouse whereParentId($value)
 * @method static Builder|Warehouse whereRead($value)
 * @method static Builder|Warehouse whereStatus($value)
 * @method static Builder|Warehouse whereSubject($value)
 * @method static Builder|Warehouse whereType($value)
 * @method static Builder|Warehouse whereUpdatedAt($value)
 * @method static Builder|Warehouse whereUserId($value)
 * @method static Builder|Warehouse whereUuid($value)
 * @mixin Eloquent
 */
class Warehouse extends Model
{
    use HasFactory;

    const TYPE_OUTCOME = 'outcome';
    const TYPE_INCOME  = 'income';

    const TYPES = [
      self::TYPE_OUTCOME => self::TYPE_OUTCOME,
      self::TYPE_INCOME  => self::TYPE_INCOME,
    ];

    protected $fillable = ['shop_product_id','user_id','note','quantity','type'];

    public function shopProduct(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class,'shop_product_id','id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
