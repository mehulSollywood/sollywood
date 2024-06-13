<?php

namespace App\Http\Resources;

use App\Models\Coupon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Coupon|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'type' => $this->when($this->type, (string) $this->type),
            'qty' => $this->when($this->qty, (int) $this->qty),
            'price' => $this->when($this->price, (double) $this->price),
            'expired_at' => $this->when($this->expired_at, $this->expired_at),
            'img' => $this->when($this->img, $this->img),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),
            'shop' => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
