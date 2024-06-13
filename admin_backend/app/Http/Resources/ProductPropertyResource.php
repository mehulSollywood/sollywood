<?php

namespace App\Http\Resources;

use App\Models\ProductProperties;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class ProductPropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ProductProperties|JsonResource $this */

        return [
            'locale' => $this->locale,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
