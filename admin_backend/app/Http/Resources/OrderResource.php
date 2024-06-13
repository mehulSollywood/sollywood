<?php

namespace App\Http\Resources;

use App\Helpers\Utility;
use App\Models\Order;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Order|JsonResource $this */

        $order = $this;
        $location = 0;

        if ($this->relationLoaded('shop')) {

            $shopLocation = [];
            $orderLocation = [];

            if (data_get($order->shop, 'location.latitude') && data_get($order->shop, 'location.longitude')) {
                $shopLocation = data_get($order->shop, 'location');
            }

            if (data_get($order?->deliveryAddress?->location, 'latitude') && data_get($order?->deliveryAddress?->location, 'longitude')) {
                $orderLocation = $order->deliveryAddress->location;
            }

            if (count($shopLocation) === 2 && count($orderLocation) === 2) {
                $location = (new Utility)->getDistance($shopLocation, $orderLocation);
            }

        }

        $couponPrice = 0;

        if ($this->relationLoaded('coupon')) {

            $couponPrice = $this->coupon?->price;

        }

        return [
            'id'                  => $this->id,
            'user_id'             => $this->when($this->user_id,$this->user_id),
            'branch_id'           => $this->when($this->branch_id,$this->branch_id),
            'price'               => $this->when($this->price, round($this->price,2)),
            'currency_price'      => $this->when($this->currency_price,(double) $this->currency_price),
            'rate'                => $this->when($this->rate,(double) $this->rate),
            'tax'                 => $this->when($this->tax,round($this->tax,2)),
            'commission_fee'      => $this->when($this->commission_fee,round($this->commission_fee,2)),
            'status'              => $this->when($this->status,$this->status),
            'delivery_fee'        => $this->delivery_fee,
            'delivery_date'       => $this->when($this->delivery_date,$this->delivery_date),
            'img'                 => $this->when($this->img,$this->img),
            'delivery_time'       => $this->when($this->delivery_time,$this->delivery_time),
            'note'                => $this->when($this->note, (string) $this->note),
            'current'             => $this->when($this->current, (bool) $this->current),
            'name'                => $this->when($this->name, $this->name),
            'phone'               => $this->when($this->phone,  $this->phone),
            'money_back'          => $this->when($this->money_back,  $this->money_back),
            'total_discount'      => round($this->total_discount,2),
            'order_details_count' => (int) $this->order_details_count,
            'created_at'          => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at'          => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'km'                  => $this->whenLoaded('shop', $location),
            'shop'                => ShopResource::make($this->whenLoaded('shop')),
            'currency'            => CurrencyResource::make($this->whenLoaded('currency')),
            'user'                => UserResource::make($this->whenLoaded('user')),
            'details'             => OrderDetailResource::collection($this->whenLoaded('orderDetails')),
            'transaction'         => TransactionResource::make($this->whenLoaded('transaction')),
            'review'              => ReviewResource::make($this->whenLoaded('review')),
            'delivery_address'    => UserAddressResource::make($this->whenLoaded('deliveryAddress')),
            'deliveryman'         => UserResource::make($this->whenLoaded('deliveryMan')),
            'delivery_type'       => DeliveryResource::make($this->whenLoaded('deliveryType')),
            'coupon'              => CouponResource::make($this->whenLoaded('coupon')),
            'refund'              => RefundResource::make($this->whenLoaded('refund')),
            'bonus_shop'          => BonusShopResource::make($this->whenLoaded('bonusShop')),
            'branch'              => BranchResource::make($this->whenLoaded('branch')),
            'galleries'           => GalleryResource::collection($this->whenLoaded('galleries')),
        ];
    }
}
