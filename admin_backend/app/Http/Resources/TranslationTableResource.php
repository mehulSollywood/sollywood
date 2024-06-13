<?php

namespace App\Http\Resources;

use App\Models\Translation;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TranslationTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Translation|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'group' => (string) $this->group,
            'key' => (string) $this->key,
            'value' => [
                'locale' => (string) $this->locale,
                'value' => (string) $this->value,
            ],
            'created_at' => optional($this->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)->format('Y-m-d H:i:s'),
        ];
    }
}
