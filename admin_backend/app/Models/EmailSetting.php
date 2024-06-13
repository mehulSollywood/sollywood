<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\EmailSetting
 *
 * @method static Builder|EmailSetting newModelQuery()
 * @method static Builder|EmailSetting newQuery()
 * @method static Builder|EmailSetting query()
 * @mixin Eloquent
 * @property int $id
 * @property int $smtp_auth
 * @property int $smtp_debug
 * @property string $host
 * @property int $port
 * @property int|null $username
 * @property string|null $password
 * @property string|null $from_to
 * @property string|null $from_site
 * @property mixed|null $ssl
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static Builder|EmailSetting whereActive($value)
 * @method static Builder|EmailSetting whereCreatedAt($value)
 * @method static Builder|EmailSetting whereDeletedAt($value)
 * @method static Builder|EmailSetting whereFromSite($value)
 * @method static Builder|EmailSetting whereFromTo($value)
 * @method static Builder|EmailSetting whereHost($value)
 * @method static Builder|EmailSetting whereId($value)
 * @method static Builder|EmailSetting wherePassword($value)
 * @method static Builder|EmailSetting wherePort($value)
 * @method static Builder|EmailSetting whereSmtpAuth($value)
 * @method static Builder|EmailSetting whereSmtpDebug($value)
 * @method static Builder|EmailSetting whereSsl($value)
 * @method static Builder|EmailSetting whereUpdatedAt($value)
 * @method static Builder|EmailSetting whereUsername($value)
 */
class EmailSetting extends Model
{

    use HasFactory,SoftDeletes;

    const TTL = 8640000000; // 100000 day

    protected $guarded = ['id'];

    protected $casts = [
        'ssl' => 'array',
    ];

    /**
     * @return mixed
     */
    public static function list(): mixed
    {
        return Cache::remember('email-settings-list', self::TTL, function () {
            return self::orderByDesc('id')->get();
        });
    }
}
