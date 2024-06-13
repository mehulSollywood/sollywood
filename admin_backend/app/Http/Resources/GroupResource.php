<?php

namespace App\Http\Resources;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Group|JsonResource $this */
        return [
            'id' => $this->id,
            'status' => $this->status,

            // Relations
            'translation' => TranslationResource::make($this->translation),
            'translations' => TranslationResource::collection($this->translations),
        ];
    }
}
