<?php

namespace App\Http\Resources;

use App\Models\UserCart;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserCartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var UserCart|JsonResource $this */

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'status' => $this->status,
            'cartDetails' => CartDetailResource::collection($this->whenLoaded('cartDetails'))
        ];
    }
}
