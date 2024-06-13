<?php

namespace App\Models;

use Database\Factories\PaymentTranslationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PaymentTranslation
 *
 * @property int $id
 * @property int $payment_id
 * @property string $locale
 * @property string $title
 * @method static PaymentTranslationFactory factory(...$parameters)
 * @method static Builder|PaymentTranslation newModelQuery()
 * @method static Builder|PaymentTranslation newQuery()
 * @method static Builder|PaymentTranslation query()
 * @method static Builder|PaymentTranslation whereId($value)
 * @method static Builder|PaymentTranslation whereLocale($value)
 * @method static Builder|PaymentTranslation wherePaymentId($value)
 * @method static Builder|PaymentTranslation whereTitle($value)
 * @mixin Eloquent
 */
class PaymentTranslation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

}
