<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ShopPayment
 *
 * @property int $id
 * @property int $payment_id
 * @property int $shop_id
 * @property int $status
 * @property string|null $client_id
 * @property string|null $secret_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $merchant_email
 * @property string|null $payment_key
 * @property-read Payment $payment
 * @method static Builder|ShopPayment newModelQuery()
 * @method static Builder|ShopPayment newQuery()
 * @method static Builder|ShopPayment query()
 * @method static Builder|ShopPayment whereClientId($value)
 * @method static Builder|ShopPayment whereCreatedAt($value)
 * @method static Builder|ShopPayment whereId($value)
 * @method static Builder|ShopPayment whereMerchantEmail($value)
 * @method static Builder|ShopPayment wherePaymentId($value)
 * @method static Builder|ShopPayment wherePaymentKey($value)
 * @method static Builder|ShopPayment whereSecretId($value)
 * @method static Builder|ShopPayment whereShopId($value)
 * @method static Builder|ShopPayment whereStatus($value)
 * @method static Builder|ShopPayment whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ShopPayment extends Model
{
    use HasFactory;
    protected $fillable = ['payment_id','shop_id','status','client_id','secret_id','merchant_email','payment_key'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
