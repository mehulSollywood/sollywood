<?php

namespace App\Http\Resources;

use App\Models\OrderTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var OrderTemplate|JsonResource $this */

        return [
            'id' => $this->id,
            'date' => $this->date,
            'order_id' => $this->order_id,
            'order' => OrderResource::make($this->whenLoaded('order')),
        ];
    }
}
