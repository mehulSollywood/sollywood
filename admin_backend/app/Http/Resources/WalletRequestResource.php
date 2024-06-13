<?php

namespace App\Http\Resources;

use App\Models\WalletRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var WalletRequest|JsonResource $this */

        return [
            'id'               => $this->id,
            'price'            => $this->price,
            'request_user_id'  => $this->request_user_id,
            'response_user_id' => $this->response_user_id,
            'message'          => $this->message,
            'status'           => $this->status,
            'own'              => $this->own,
            'created_at'       => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at'       => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            'request_user'  => UserResource::make($this->whenLoaded('requestUser')),
            'response_user' => UserResource::make($this->whenLoaded('responseUser')),
        ];
    }
}
