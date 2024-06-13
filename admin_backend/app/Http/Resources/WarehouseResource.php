<?php

namespace App\Http\Resources;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Warehouse|JsonResource $this */

        return [
            'id'              => $this->id,
            'quantity'        => $this->quantity,
            'note'            => $this->note,
            'type'            => $this->type,
            'user_id'         => $this->user_id,
            'shop_product_id' => $this->shop_product_id,

            'user'            => UserResource::make($this->whenLoaded('user')),
            'shop_product'    => ShopProductResource::make($this->whenLoaded('shopProduct')),

        ];
    }
}
