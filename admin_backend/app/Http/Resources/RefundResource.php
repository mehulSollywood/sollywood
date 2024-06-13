<?php

namespace App\Http\Resources;

use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Refund $this */

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'message_seller' => $this->message_seller,
            'message_user' => $this->message_user,
            'image' => $this->image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'order' => OrderResource::make($this->whenLoaded('order')),
            'user' => UserResource::make($this->whenLoaded('user')),
            'galleries'     => GalleryResource::collection($this->whenLoaded('galleries')),

        ];
    }
}
