<?php

namespace App\Repositories\UserRepository;

use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\UserRepoInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends CoreRepository implements UserRepoInterface
{

    protected function getModelClass(): string
    {
        return User::class;
    }

    public function userById(int $id): User
    {
        /** @var User $user */

        $user = $this->model()
            ->with('wallet.histories', 'shop', 'addresses', 'emailSubscription','invite')
            ->withCount([
                'orders' => fn($q) => $q->where('status', Order::DELIVERED)
            ])->withSum([
                'orders' => fn($q) => $q->where('status', Order::DELIVERED)
            ], 'price')
            ->find($id);

        if (empty($user) || empty($user->wallet)) {
            return $user;
        }

        $rate         = $user->wallet->currency?->rate ?? 1;

        $fromTopUp    = $user->wallet->histories?->where('type','referral_from_topup');
        $fromWithdraw = $user->wallet->histories?->where('type','referral_from_withdraw');
        $toTopUp      = $user->wallet->histories?->where('type','referral_to_withdraw');
        $toWithdraw   = $user->wallet->histories?->where('type','referral_to_topup');

        $user->setAttribute('referral_from_topup_price', $fromTopUp->sum('price') * $rate)
            ->setAttribute('referral_from_withdraw_price', $fromWithdraw->sum('price') * $rate)
            ->setAttribute('referral_to_withdraw_price', $toWithdraw->sum('price') * $rate)
            ->setAttribute('referral_to_topup_price', $toTopUp->sum('price') * $rate)
            ->setAttribute('referral_from_topup_count', $fromTopUp->count())
            ->setAttribute('referral_from_withdraw_count', $fromWithdraw->count())
            ->setAttribute('referral_to_withdraw_count', $toWithdraw->count())
            ->setAttribute('referral_to_topup_count', $toTopUp->count());

        request()->offsetSet('referral', 1);

        return $user;
    }

    public function userByUUID(string $uuid)
    {
        return $this->model()->with([
            'shop.translation',
            'wallet',
            'point',
            'addresses',
            'deliveryManSetting',
            'invitations' => fn($q) => $q->whereHas('shop'),
            'invitations.shop.translation' => fn($q) => $q->select(['id', 'shop_id', 'locale', 'title'])
                ->where('locale', $this->language),
        ])->firstWhere('uuid', $uuid);
    }

    public function usersPaginate(string $perPage, array $array = [], $active = null)
    {
        return $this->model()
            ->filter($array)
            ->with(['shop', 'wallet'])
            ->when(isset($array['role']), function ($q) use ($array) {
                $q->whereHas('roles', function ($q) use ($array) {
                    $q->where('name', $array['role']);
                });
            })
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->where(function ($query) use ($array) {
                    $query->where('firstname', 'LIKE', '%' . $array['search'] . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $array['search'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $array['search'] . '%')
                        ->orWhere('phone', 'LIKE', '%' . $array['search'] . '%');
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->when($active, function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->when(isset($array['walletSort']), function ($q) use ($array) {
                $q->whereHas('wallet', function ($q) use ($array) {
                    $q->orderBy($array['walletSort'], $array['sort'] ?? 'desc');
                });
            })
            ->paginate($perPage);
    }

    public function usersSearch($search, $active = null, $roles = [])
    {
        return $this->model()->query()
            ->where('id', '!=', auth('sanctum')->id())
            ->when(count($roles) > 0, function ($q) use ($roles) {
                $q->whereHas('roles', function ($q) use ($roles) {
                    $q->whereIn('name', $roles);
                });
            })
            ->where(function ($query) use ($search) {
                $query->where('firstname', 'LIKE', '%' . $search . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
            })->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })->latest()->take(10)->get();
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function deliveryMans(array $filter): LengthAwarePaginator
    {

        $filter['role'] = 'deliveryman';

        if (data_get($filter, 'empty-setting')) {
            $filter['online'] = false;
        }

        return User::filter($filter)
            ->with([
                'roles',
                'reviews',
                'deliveryManSetting',
                'deliveryManOrders' => fn($q) => $q->select([
                    'id', 'price', 'status','delivery_address_id',
                    'delivery_fee', 'rate', 'delivery_date', 'delivery_time', 'shop_id', 'user_id','deliveryman'
                ])->when(data_get($filter, 'statuses'), function ($query, $statuses) {

                    if (!is_array($statuses)) {
                        return $query;
                    }

                    $statuses = array_intersect($statuses, Order::STATUS);

                    return $query->whereIn('status', $statuses);
                }),
                'deliveryManOrders.shop:id,uuid,price,price_per_km,logo_img,location',
                'deliveryManOrders.user:id,img,firstname,lastname',
                'deliveryManOrders.shop.translation',
                'wallet',
            ])
            ->withAvg('reviews','rating')
            ->withCount('deliveryManOrders')
            ->withSum('deliveryManOrders', 'price')
            ->withSum('wallet', 'price')
            ->orderByDesc('id')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @return Collection|Notification[]
     */
    public function usersNotifications(): array|Collection
    {
        return Notification::get();
    }
}
