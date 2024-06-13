<?php

namespace App\Http\Resources;

use App\Models\RecipeNutrition;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class RecipeNutritionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var RecipeNutrition|JsonResource $this */

        return [
            'id' => $this->id,
            'weight' => $this->weight,
            'percentage' => $this->percentage,
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
