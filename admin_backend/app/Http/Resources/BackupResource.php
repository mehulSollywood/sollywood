<?php

namespace App\Http\Resources;

use App\Models\BackupHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BackupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var BackupHistory|JsonResource $this */

        return [
            'id'         => (int) $this->id,
            'title'      => (string) $this->title,
            'status'     => (bool) $this->status,
            'path'       =>  '/storage/laravel-backup/', // (string) $this->path,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),

            // Relations
            'user'       => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
