<?php

namespace App\Http\Resources;

use App\Models\UserAddress;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var UserAddress|JsonResource $this */

        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'address' => (string) $this->address,
            'location' => [
                'latitude' => $this->location['latitude'],
                'longitude' => $this->location['longitude'],
            ],
            'default' => (boolean) $this->default,
            'active' => (boolean) $this->active,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),
        ];
    }
}
