<?php

namespace App\Http\Resources;

use App\Models\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ShopCategory|JsonResource $this */

        return [
            'id' => $this->id,
            'category' => CategoryResource::make($this->whenLoaded('category')),
        ];
    }
}
