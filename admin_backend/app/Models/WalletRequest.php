<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\WalletRequest
 *
 * @property int $id
 * @property int $request_user_id
 * @property int $response_user_id
 * @property double $price
 * @property string $message
 * @property string $status
 * @property boolean $own
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $requestUser
 * @property-read User|null $responseUser
 */
class WalletRequest extends Model
{
    use HasFactory;

    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';

    protected $fillable = [
        'price',
        'request_user_id',
        'response_user_id',
        'message',
        'status',
    ];
    public function requestUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'request_user_id', 'id');
    }

    public function responseUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'response_user_id', 'id');
    }

    public function getOwnAttribute($value): bool
    {
        if ($this->request_user_id == auth('sanctum')->user()->id) {
                return true;
        }
        return false;
    }
}
