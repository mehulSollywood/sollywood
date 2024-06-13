<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ShopProduct|JsonResource $this */
        /** @var Product $product */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'product' => ProductSearchResource::make($this->whenLoaded('product')),
        ];
    }

}
