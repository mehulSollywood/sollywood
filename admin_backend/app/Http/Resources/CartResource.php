<?php

namespace App\Http\Resources;

use App\Models\Cart;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Cart|JsonResource $this */
        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'shop_id' => $this->shop_id,
            'status' => (boolean) $this->status,
            'total_price' => $this->total_price,
            'together' => (boolean) $this->together,
            'created_at' => $this->created_at,
            'userCarts' => UserCartResource::collection($this->whenLoaded('userCarts'))
        ];
    }
}
