<?php

namespace App\Http\Resources;

use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopProductSelectResource extends JsonResource
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
        return [
            'id' => $this->id,
            'product' => ProductSelectResource::make($this->whenLoaded('product')),
        ];
    }

}
