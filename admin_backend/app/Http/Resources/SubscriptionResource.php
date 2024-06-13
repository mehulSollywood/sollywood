<?php

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Subscription|JsonResource $this */

        return [
            "id" => (int) $this->id,
            "type" => (string) $this->type,
            "price" => (double) $this->price,
            "month" => (int) $this->month,
            "active" => (boolean) $this->active,
            "created_at" => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            "updated_at" => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
