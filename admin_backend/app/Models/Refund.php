<?php

namespace App\Models;

use App\Traits\Loadable;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\Refund
 *
 * @method whenLoaded(string $string)
 * @property mixed $id
 * @property mixed $order_id
 * @property mixed $user_id
 * @property mixed $status
 * @property mixed $message_seller
 * @property mixed $message_user
 * @property mixed $image
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read Order $order
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Refund filter($collection)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund newQuery()
 * @method static Builder|Refund onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund query()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereMessageSeller($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereMessageUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereUserId($value)
 * @method static Builder|Refund withTrashed()
 * @method static Builder|Refund withoutTrashed()
 * @mixin Eloquent
 */
class Refund extends Model
{
    use HasFactory,Loadable,SoftDeletes;

    const PENDING = 'pending';
    const CANCELED = 'canceled';
    const ACCEPTED = 'accepted';

    const STATUS = [
        self::PENDING,
        self::CANCELED,
        self::ACCEPTED
    ];
    protected $fillable = ['order_id','user_id','status','message_seller','message_user', 'image'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter($query,$collection)
    {
        $query->when(isset($collection['status']),function ($q) use ($collection){
            $q->where('status',$collection['status']);
        })->when(isset($collection['start_date']),function ($q) use ($collection){
            $q->whereDate('created_at','>=',$collection['start_date']);
        })->when(isset($collection['end_date']),function ($q) use ($collection){
            $q->whereDate('created_at','<=',$collection['end_date']);
        })->when(isset($collection['search']),function ($q) use ($collection){
            $q->whereHas('user',function ($q) use ($collection){
                $q->where('firstname', 'LIKE', '%' . $collection['search'] . '%')
                ->orWhere('lastname', 'LIKE', '%' . $collection['search'] . '%')
                ->orWhere('email', 'LIKE', '%' . $collection['search'] . '%')
                ->orWhere('phone', 'LIKE', '%' . $collection['search'] . '%');
            });
        });
    }

}
