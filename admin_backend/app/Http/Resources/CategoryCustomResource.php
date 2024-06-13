<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryCustomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Category|JsonResource $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'keywords' => $this->when($this->keywords, (string) $this->keywords),
            'referralPercentage' => $this->when($this->referralPercentage, (int) $this->referralPercentage),
            'gstPercentage' => $this->when($this->gstPercentage, (int) $this->gstPercentage),
            'parent_id' => (int) $this->parent_id,
            'img' => $this->when(isset($this->img), (string) $this->img),
            'slug' => $this->when(isset($this->slug), (string) $this->slug),
            'products_count' =>  $this->when($this->products_count, (int) $this->products_count),
            'shop_product' => ShopProductResource::collection($this->whenLoaded('shopProduct')),

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
        ];
    }
}
