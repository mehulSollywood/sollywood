<?php

namespace App\Http\Resources;

use App\Models\OrderDetail;
use App\Models\ProductTranslation;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var OrderDetail|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'origin_price' => (double) $this->origin_price,
            'total_price' => (double) $this->total_price,
            'bonus' => $this->bonus,
            'tax' => round($this->tax,2),
            'quantity' => (string) $this->quantity,
            'discount' => (double) $this->discount,
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
            'shopProduct' => ShopProductResource::make($this->whenLoaded('shopProduct')),

        ];
    }
}
