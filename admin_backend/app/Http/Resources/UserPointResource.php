<?php

namespace App\Http\Resources;

use App\Models\UserPoint;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var UserPoint|JsonResource $this */

        return [
            'user_id' => (int) $this->user_id,
            'price' => (string) $this->price
        ];
    }
}
