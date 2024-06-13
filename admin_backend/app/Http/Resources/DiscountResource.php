<?php

namespace App\Http\Resources;

use App\Models\Discount;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class DiscountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Discount|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'shop_id' => (int) $this->shop_id,
            'type' => (string) $this->type,
            'price' => (double) $this->price,
            'start' => (string) $this->start,
            'end' => $this->end ?? null,
            'active' => (boolean) $this->active,
            'img' => $this->img,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'products' => ShopProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
