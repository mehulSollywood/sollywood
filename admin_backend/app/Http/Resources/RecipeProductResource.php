<?php

namespace App\Http\Resources;

use App\Models\RecipeProduct;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RecipeProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var RecipeProduct|JsonResource $this */

        return [
            'id' => $this->id,
            'measurement' => $this->measurement,
            'shopProduct' => ShopProductResource::make($this->whenLoaded('shopProduct'))
        ];
    }
}
