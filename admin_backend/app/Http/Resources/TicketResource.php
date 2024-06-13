<?php

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Ticket|JsonResource $this */

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'created_by' => $this->created_by,
            'user_id' => $this->user_id,
            'order_id' => $this->order_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'subject' => $this->subject,
            'content' => $this->content,
            'status' => $this->status,
            'read' => $this->read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
