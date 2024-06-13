<?php

namespace App\Http\Resources;

use App\Models\RecipeCategory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RecipeCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var RecipeCategory|JsonResource $this */

        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'image' => $this->image,
            'parent_id' => $this->parent_id,
            'recipes_count' => $this->recipeCount(),

            'child' => RecipeCategoryResource::collection($this->whenLoaded('child')),
            'recipes' => RecipeResource::collection($this->whenLoaded('recipes')),
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }

    public function recipeCount()
    {
        $childs = $this->child;
        $childCount = 0;
        foreach ($childs as $child)
        {
            $childCount += $child->recipes->count();
        }
        return $childCount;
    }


}
