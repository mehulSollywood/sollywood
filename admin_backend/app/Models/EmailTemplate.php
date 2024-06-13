<?php

namespace App\Models;

use App\Traits\Loadable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * App\Models\EmailTemplate
 *

 * @mixin Eloquent
 * @property int $id
 * @property int $email_setting_id
 * @property string $subject
 * @property string $body
 * @property string $alt_body
 * @property int $status
 * @property string $send_to
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Gallery $galleries
 * @method static Builder|EmailTemplate whereAltBody($value)
 * @method static Builder|EmailTemplate whereBody($value)
 * @method static Builder|EmailTemplate whereCreatedAt($value)
 * @method static Builder|EmailTemplate whereDeletedAt($value)
 * @method static Builder|EmailTemplate whereEmailSettingId($value)
 * @method static Builder|EmailTemplate whereId($value)
 * @method static Builder|EmailTemplate whereSendTo($value)
 * @method static Builder|EmailTemplate whereStatus($value)
 * @method static Builder|EmailTemplate whereSubject($value)
 * @method static Builder|EmailTemplate whereType($value)
 * @method static Builder|EmailTemplate whereUpdatedAt($value)
 * @method static Builder|EmailTemplate newModelQuery()
 * @method static Builder|EmailTemplate newQuery()
 * @method static Builder|EmailTemplate query()
 */
class EmailTemplate extends Model
{
    use HasFactory, Loadable, SoftDeletes;
    protected $guarded = ['id'];

    const TYPE_ORDER        = 'order';
    const TYPE_SUBSCRIBE    = 'subscribe';
    const TYPE_VERIFY       = 'verify';

    const TYPES = [
        self::TYPE_ORDER        => self::TYPE_ORDER,
        self::TYPE_SUBSCRIBE    => self::TYPE_SUBSCRIBE,
        self::TYPE_VERIFY       => self::TYPE_VERIFY,
    ];

    public function emailSetting(): BelongsTo
    {
        return $this->belongsTo(EmailSetting::class);
    }
}
