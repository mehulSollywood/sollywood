<?php

namespace App\Http\Resources;

use App\Models\ShopBrand;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ShopBrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ShopBrand|JsonResource $this */

        return [
            'id' => $this->id,
            'brand' => BrandResource::make($this->whenLoaded('brand'))
        ];
    }
}
