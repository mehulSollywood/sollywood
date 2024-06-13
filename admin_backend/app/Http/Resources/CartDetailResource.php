<?php

namespace App\Http\Resources;

use App\Models\CartDetail;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class CartDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var CartDetail|JsonResource $this */

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'bonus' => (bool)$this->bonus,
            'price' => $this->price,
            'user_cart_uuid' => $this?->userCart?->uuid,
            'shopProduct' => ShopProductResource::make($this->whenLoaded('shopProduct'))
        ];
    }
}
