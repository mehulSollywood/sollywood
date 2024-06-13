<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\UserCart
 *
 * @property int $id
 * @property int $cart_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $uuid
 * @property int|null $user_id
 * @property int $status
 * @property-read Cart $cart
 * @property-read Collection|CartDetail[] $cartDetails
 * @property-read int|null $cart_details_count
 * @method static Builder|UserCart newModelQuery()
 * @method static Builder|UserCart newQuery()
 * @method static Builder|UserCart query()
 * @method static Builder|UserCart whereCartId($value)
 * @method static Builder|UserCart whereCreatedAt($value)
 * @method static Builder|UserCart whereId($value)
 * @method static Builder|UserCart whereName($value)
 * @method static Builder|UserCart whereStatus($value)
 * @method static Builder|UserCart whereUpdatedAt($value)
 * @method static Builder|UserCart whereUserId($value)
 * @method static Builder|UserCart whereUuid($value)
 * @mixin Eloquent
 */
class UserCart extends Model
{
    use HasFactory;
    protected $fillable = ['id','name','uuid','user_id','status'];

    public function cartDetails(): HasMany
    {
        return $this->hasMany(CartDetail::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}
