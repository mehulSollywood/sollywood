<?php

namespace App\Http\Resources;

use App\Models\BonusProduct;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class BonusProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var BonusProduct|JsonResource $this */

        return [
            'id' => $this->id,
            'shop_product_id' => $this->shop_product_id,
            'bonus_product_id' => $this->bonus_product_id,
            'bonus_quantity' => $this->bonus_quantity,
            'shop_product_quantity' => $this->shop_product_quantity,
            'status' => (bool)$this->status,
            'expired_at' => Carbon::parse($this->expired_at)->format('Y-m-d'),
            'created_at' => $this->created_at,

            'shop_product' => ShopProductResource::make($this->whenLoaded('shopProduct')),
            'bonus_product' => ShopProductResource::make($this->whenLoaded('bonusProduct')),
        ];
    }
}
