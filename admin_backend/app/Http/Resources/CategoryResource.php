<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Category|JsonResource $this */

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'keywords' => $this->when($this->keywords, (string) $this->keywords),
            'referralPercentage' => $this->when($this->referralPercentage, (int) $this->referralPercentage),
            'gstPercentage' => $this->when($this->gstPercentage, (int) $this->gstPercentage),
            'slug' => $this->when($this->slug, (string) $this->slug),
            'parent_id' => (int) $this->parent_id,
            'type' => $this->when($this->type, (string) $this->type),
            'img' => $this->when(isset($this->img), (string) $this->img),
            'active' => $this->when($this->active, (bool) $this->active),
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' =>  $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'products_count' =>  $this->when($this->products_count, (int) $this->products_count),
            'shop_product' => ShopProductResource::collection($this->whenLoaded('shopProduct')),

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),

        ];
    }
}
