<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
       
        /** @var User $this */
        return [
            'id'                            => $this->id,
            'uuid'                          => $this->when($this->uuid, $this->uuid),
            'firstname'                     => $this->firstname ?? '',
            'lastname'                      => $this->lastname ?? '',
            'email'                         => $this->when($this->email, (string) $this->email),
            'phone'                         => $this->when($this->phone, (string) $this->phone),
            'birthday'                      => $this->when($this->birthday, optional($this->birthday)->format('Y-m-d H:i:s')),
            'gender'                        => $this->when($this->gender, $this->gender),
            'device_id'                     => request('device_id') ?: $this->device_id,          
            'my_referral'                   => $this->when($this->my_referral, $this->my_referral),
            'referral'                      => $this->when($this->referral, $this->referral),
            'email_verified_at'             => $this->when($this->email_verified_at, optional($this->email_verified_at)->format('Y-m-d H:i:s')),
            'phone_verified_at'             => $this->when($this->phone_verified_at, optional($this->phone_verified_at)->format('Y-m-d H:i:s')),
            'registered_at'                 => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'active'                        => $this->when(isset($this->active), (bool) $this->active),
            'img'                           => $this->when($this->img, $this->img),
            'role'                          => $this->when($this->role, $this->role),

            'created_at'                    => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s')),
            'updated_at'                    => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s')),
            'deleted_at'                    => $this->when($this->deleted_at, $this->deleted_at?->format('Y-m-d H:i:s')),
            'referral_from_topup_price'     => $this->when(request('referral'), $this->referral_from_topup_price),
            'referral_from_withdraw_price'  => $this->when(request('referral'), $this->referral_from_withdraw_price),
            'referral_to_withdraw_price'    => $this->when(request('referral'), $this->referral_to_withdraw_price),
            'referral_to_topup_price'       => $this->when(request('referral'), $this->referral_to_topup_price),
            'referral_from_topup_count'     => $this->when(request('referral'), $this->referral_from_topup_count),
            'referral_from_withdraw_count'  => $this->when(request('referral'), $this->referral_from_withdraw_count),
            'referral_to_withdraw_count'    => $this->when(request('referral'), $this->referral_to_withdraw_count),
            'referral_to_topup_count'       => $this->when(request('referral'), $this->referral_to_topup_count),

            'deliveryman_orders'            => OrderResource::collection($this->whenLoaded('deliveryManOrders')),
            'notifications'                 => $this->whenLoaded('notifications'),

            'addresses'                     => UserAddressResource::collection($this->whenLoaded('addresses')),
            'shop'                          => ShopResource::make($this->whenLoaded('shop')),
            'wallet'                        => WalletResource::make($this->whenLoaded('wallet')),
            'point'                         => UserPointResource::make($this->whenLoaded('point')),
            'subscription'                  => SubscriptionResource::make($this->whenLoaded('emailSubscription')),
            'invitation'                    => InviteResource::make($this->whenLoaded('invite')),
            'delivery_man_setting'          => DeliveryManSettingResource::make($this->whenLoaded('deliveryManSetting')),
        ];
    }
}
