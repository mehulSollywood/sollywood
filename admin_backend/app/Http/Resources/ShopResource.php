<?php

namespace App\Http\Resources;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Shop $this */
        return [
            'id'  => $this->id,
            'uuid' => $this->when($this->uuid,$this->uuid),
            'user_id' =>  $this->when($this->user_id,$this->user_id),
            'tax' => $this->when($this->tax,round($this->tax,2)),
            'delivery_range' => $this->when($this->delivery_range,$this->delivery_range),
            'percentage' => $this->when($this->percentage,$this->percentage),
            'location' => $this->when($this->location,[
                'latitude' => $this->when($this->location,$this->location['latitude'] ?? null),
                'longitude' => $this->when($this->location,$this->location['longitude'] ?? null),
            ]),
            'price' => $this->when($this->rate_price, $this->rate_price),
            'slug' => $this->when($this->slug, $this->slug),
            'type_of_business' => $this->when($this->type_of_business, $this->type_of_business),
            'category' => $this->when($this->category, $this->category),
            'commission' => $this->when($this->commission, $this->commission),
            'adhar' => $this->when($this->adhar, $this->adhar),
            'pan' => $this->when($this->pan, $this->pan),
            'business_res_certi' => $this->when($this->business_res_certi, $this->business_res_certi),
            'gst' => $this->when($this->gst, $this->gst),
            'price_per_km' => $this->when($this->rate_price_per_km, $this->rate_price_per_km),
            'group_id' => $this->when($this->group_id,$this->group_id),
            'phone' =>  $this->when($this->phone,$this->phone),
            'show_type' => $this->when($this->show_type,(bool) $this->show_type),
            'open' => $this->when($this->open,(bool) $this->open),
            'visibility' => $this->when($this->visibility,(bool) $this->visibility),
            'background_img' => $this->when($this->background_img,$this->background_img),
            'logo_img' => $this->when($this->logo_img,$this->logo_img),
            'min_amount' => $this->when($this->min_amount,$this->min_amount),
            'status' => $this->when($this->status,$this->status),
            'status_note' => $this->when($this->status_note,$this->status_note),
            'delivery_time'     => $this->when($this->delivery_time, $this->delivery_time),
            'invite_link' => $this->when(auth('sanctum')->check() && auth('sanctum')->user()->hasRole('seller'), '/shop/invitation/' .$this->uuid . '/link'),
            'rating_avg' => $this->when($this->reviews_avg_rating, number_format($this->reviews_avg_rating, 2)),
            'reviews_count' => $this->when($this->reviews_count, (int) $this->reviews_count),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),

            'tags' => ShopTagResource::collection($this->whenLoaded('tags')),
            'shop_payments' => ShopPaymentResource::collection($this->whenLoaded('shopPayments')),
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'seller' => UserResource::make($this->whenLoaded('seller')),
            'deliveries' => DeliveryResource::collection($this->whenLoaded('deliveries')),
            'subscription' => $this->whenLoaded('subscription'),
            'group' => GroupResource::make($this->whenLoaded('group')),
            'branches' => BranchResource::collection($this->whenLoaded('branches')),
            'shop_working_days' => ShopWorkingDayResource::collection($this->whenLoaded('workingDays')),
            'shop_closed_date'  => ShopClosedDateResource::collection($this->whenLoaded('closedDates')),

        ];
    }
}
