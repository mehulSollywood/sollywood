<?php

namespace App\Http\Resources;

use App\Models\UserGiftCart;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserGiftCartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var UserGiftCart $this */

        return [
            'id' => $this->id,
            'price' => $this->price,
            'shop_product_id' => $this->shop_product_id,

            'shop_product' => ShopProductResource::make($this->whenLoaded('shopProduct'))
        ];
    }
}
