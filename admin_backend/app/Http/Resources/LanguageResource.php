<?php

namespace App\Http\Resources;

use App\Models\Language;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class LanguageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Language|JsonResource $this */

        return [
            'id' => $this->id,
            'title' => $this->title,
            'locale' => $this->locale,
            'backward' => $this->backward,
            'default' => $this->default,
            'active' => $this->active,
            'img' => $this->img,
        ];
    }
}
