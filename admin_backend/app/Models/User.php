<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 * /**
 * App\Models\User
 *
 * @property int $id
 * @property string $uuid
 * @property string $firstname
 * @property string|null $lastname
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon|null $birthday
 * @property string $gender
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property string|null $ip_address
 * @property int $active
 * @property string|null $img
 * @property array|null $firebase_token
 * @property string|null $device_id
 * @property string|null $password
 * @property string|null $remember_token
 * @property string|null $name_or_email
 * @property string|null $verify_token
 * @property string|null $referral
 * @property string|null $my_referral
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection|Gallery[] $galleries
 * @property-read int|null $galleries_count
 * @property-read mixed $role
 * @property-read Collection|Invitation[] $invitations
 * @property-read int|null $invitations_count
 * @property-read Invitation|null $invite
 * @property-read Collection|Banner[] $likes
 * @property-read int|null $likes_count
 * @property-read Shop|null $moderatorShop
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection|Review[] $assignReviews
 * @property-read int|null $assign_reviews_count
 * @property-read Collection|Notification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|OrderDetail[] $orderDetails
 * @property-read int|null $order_details_count
 * @property-read int|null $orders_sum_total_price
 * @property-read Collection|Order[] $orders
 * @property-read int|null $orders_count
 * @property-read Collection|Order[] $deliveryManOrders
 * @property-read int|null $delivery_man_orders_count
 * @property-read int|null $delivery_man_orders_sum_total_price
 * @property-read int|null $reviews_avg_rating
 * @property-read int|null $assign_reviews_avg_rating
 * @property-read Collection|Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection|Permission[] $transactions
 * @property-read int|null $transactions_count
 * @property-read UserPoint|null $point
 * @property-read Collection|PointHistory[] $pointHistory
 * @property-read int|null $point_history_count
 * @property-read Collection|Role[] $roles
 * @property-read int|null $roles_count
 * @property-read Shop|null $shop
 * @property-read DeliveryManSetting|null $deliveryManSetting
 * @property-read EmailSubscription|null $emailSubscription
 * @property-read Collection|SocialProvider[] $socialProviders
 * @property-read int|null $social_providers_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int $payment_process_count
 * @property-read int|null $tokens_count
 * @property-read Wallet|null $wallet
 * @property int $activities_count
 * @property-read int|null $referral_from_topup_price
 * @property-read int|null $referral_from_withdraw_price
 * @property-read int|null $referral_to_withdraw_price
 * @property-read int|null $referral_to_topup_price
 * @property-read int|null $referral_from_topup_count
 * @property-read int|null $referral_from_withdraw_count
 * @property-read int|null $referral_to_withdraw_count
 * @property-read int|null $referral_to_topup_count
 * @method static UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User onlyTrashed()
 * @method static Builder|User permission($permissions)
 * @method static Builder|User query()
 * @method static Builder|User filter($filter)
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User whereActive($value)
 * @method static Builder|User whereBirthday($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirebaseToken($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereImg($value)
 * @method static Builder|User whereIpAddress($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User wherePhoneVerifiedAt($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUuid($value)
 * @method static Builder|User withTrashed()
 * @method static Builder|User withoutTrashed()
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use Loadable;
    use SoftDeletes;
    use Reviewable;

    const ROLE_DELIVERYMAN = 'deliveryman';
    const ROLE_SELLER      = 'seller';
    const ROLE_MODERATOR   = 'moderator';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'birthday',
        'gender',
        'email',
        'phone',
        'img',
        'referral',
        'password',
        'firebase_token',
        'device_id',
        'email_verified_at',
        'phone_verified_at',
        'verify_token',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'birthday'          => 'date',
       // 'firebase_token'    => 'array',
    ];

    public function isOnline(): bool
    {
        return Cache::has('user-online-' . $this->id);
    }

    public function getRoleAttribute(): string
    {
        return $this->role = $this->roles[0]->name ?? 'no role';
    }

    public function shop(): HasOne
    {
        return $this->hasOne(Shop::class);
    }

    public function invite(): HasOne
    {
        return $this->hasOne(Invitation::class);
    }

    public function moderatorShop(): HasOneThrough
    {
        return $this->hasOneThrough(Shop::class, Invitation::class,
            'user_id', 'id', 'id', 'shop_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function socialProviders(): HasMany
    {
        return $this->hasMany(SocialProvider::class,'user_id','id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class,'user_id');
    }

    public function orderDetails(): HasManyThrough
    {
        return $this->hasManyThrough(OrderDetail::class,Order::class);
    }

    public function point(): HasOne
    {
        return $this->hasOne(UserPoint::class, 'user_id');
    }

    public function pointHistory(): HasMany
    {
        return $this->hasMany(PointHistory::class, 'user_id');
    }

    public function getNameOrEmailAttribute(): ?string
    {
        return $this->firstname ?? $this->email;
    }

    public function emailSubscription(): HasOne
    {
        return $this->hasOne(EmailSubscription::class);
    }

    public function deliveryManOrders(): HasMany
    {
        return $this->hasMany(Order::class,'deliveryman','id');
    }

    public function deliveryManSetting(): HasOne
    {
        return $this->hasOne(DeliveryManSetting::class, 'user_id');
    }

    public function assignReviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'assignable');
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, NotificationUser::class)
            ->as('notification')
            ->withPivot('active');
    }

    public function paymentProcess(): HasMany
    {
        return $this->hasMany(PaymentProcess::class);
    }



    public function scopeFilter($query, array $filter) {

        $query->when(data_get($filter, 'role'), function ($query, $role) {
            $query->when(
                $role === 'deliveryman',
                fn($q) => $q->role('deliveryman'),
                fn($q) => $q->whereHas('roles', fn($q) => $q->where('name', $role)),
            );
        })
            ->when(data_get($filter, 'roles'), function ($q, $roles) {
                $q->whereHas('roles', function ($q) use($roles) {
                    $q->whereIn('name', is_array($roles) ? $roles : [$roles]);
                });
            })
            ->when(data_get($filter, 'shop_id'), function ($query, $shopId) {
                $query->whereHas('invitations', fn($q) => $q->where('shop_id', $shopId));
            })
            ->when(data_get($filter, 'search'), function ($q, $search) {
                $q->where(function($query) use ($search) {
                    $query
                        ->where('id',           'LIKE', "%$search%")
                        ->orWhere('firstname',  'LIKE', "%$search%")
                        ->orWhere('lastname',   'LIKE', "%$search%")
                        ->orWhere('email',      'LIKE', "%$search%")
                        ->orWhere('phone',      'LIKE', "%$search%");
                });
            })
            ->when(data_get($filter, 'statuses'), function ($query, $statuses) use ($filter) {

                if (!is_array($statuses)) {
                    return $query;
                }

                $statuses = array_intersect($statuses, Order::STATUS);

                return $query->when(data_get($filter, 'role') === 'deliveryman',
                    fn($q) => $q->whereHas('deliveryManOrders', fn($q) => $q->whereIn('status', $statuses)),
                    fn($q) => $q->whereHas('orders', fn($q) => $q->whereIn('status', $statuses)),
                );
            })
            ->when(data_get($filter, 'date_from'), function ($query, $dateFrom) use ($filter) {

                $dateFrom = date('Y-m-d', strtotime($dateFrom . ' -1 day'));
                $dateTo = data_get($filter, 'date_to', date('Y-m-d'));

                $dateTo = date('Y-m-d', strtotime($dateTo . ' +1 day'));

                return $query->when(data_get($filter, 'role') === 'deliveryman',
                    fn($q) => $q->whereHas('deliveryManOrders',
                        fn($q) => $q->where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)
                    ),
                    fn($q) => $q->whereHas('orders',
                        fn($q) => $q->where('created_at', '>=', $dateFrom)->where('created_at', '<=', $dateTo)
                    ),
                );
            })
            ->when(isset($filter['online']) || data_get($filter, 'type_of_technique'), function ($query) use($filter) {
                $query->whereHas('deliveryManSetting', function ($query) use($filter) {
                    $online = data_get($filter, 'online');

                    $query
                        ->when(isset($online), function ($q) use($online) {
                            $q->where('online',$online)->where('location', '!=', null);
                        });

                });

            })
            ->when(isset($filter['active']), fn($q) => $q->where('active', $filter['active']))
            ->when(data_get($filter, 'exist_token'), fn($query) => $query->whereNotNull('firebase_token'))
            ->when(data_get($filter, 'walletSort'), function ($q, $walletSort) use($filter) {
                $q->whereHas('wallet', function ($q) use($walletSort, $filter) {
                    $q->orderBy($walletSort, data_get($filter, 'sort', 'desc'));
                });
            })
            ->when(data_get($filter, 'empty-setting'), function (Builder $query) {
                $query->whereHas('deliveryManSetting', fn($q) => $q, '=', '0');
            })
            ->when(data_get($filter,'column'), function ($query, $column) use($filter) {
                $addIfDeliveryMan = '';

                if (data_get($filter, 'role') === 'deliveryman') {
                    $addIfDeliveryMan .= 'delivery_man_';
                }

                switch ($column) {
                    case 'rating':
                        $column = 'reviews_avg_rating';
                        break;
                    case 'count':
                        $column = $addIfDeliveryMan . 'orders_count';
                        break;
                    case 'sum':
                        $column = $addIfDeliveryMan . 'orders_sum_total_price';
                        break;
                    case 'wallet_sum':
                        $column = 'wallet_sum_price';
                        break;
                }
                $query->orderBy($column, data_get($filter, 'sort', 'desc'));

                if (data_get($filter, 'by_rating')) {

                    if (data_get($filter, 'by_rating') === 'top') {
                        return $query->having('reviews_avg_rating', '>=', 3.99);
                    }

                    return $query->having('reviews_avg_rating', '<', 3.99);
                }

                return $query;
            });

    }
}
