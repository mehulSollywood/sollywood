<?php

namespace App\Http\Resources;

use App\Models\Delivery;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Delivery|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'shop_id' => (int) $this->shop_id,
            'type' => (string) $this->type,
            'price' => (double) $this->price,
            'times' => $this->times,
            'note' => (string) $this->note,
            'active' => (bool) $this->active,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // Relation
            'translation' => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
        ];
    }
}
