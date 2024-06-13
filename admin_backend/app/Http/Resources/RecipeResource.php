<?php

namespace App\Http\Resources;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Recipe|JsonResource $this */

        return [
            'id' => $this->id,
            'image' => $this->image,
            'status' => $this->status,
            'active_time' => $this->active_time,
            'total_time' => $this->total_time,
            'calories' => $this->calories,
            'shop_id' => $this->shop_id,
            'shop' => ShopResource::make($this->whenLoaded('shop')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'nutritions' => RecipeNutritionResource::collection($this->whenLoaded('nutritions')),
            'instructions' => RecipeInstructionResource::collection($this->whenLoaded('instructions')),
            'products' => RecipeProductResource::collection($this->whenLoaded('products')),
            'category' => RecipeCategoryResource::make($this->whenLoaded('recipeCategory'))
        ];
    }
}
