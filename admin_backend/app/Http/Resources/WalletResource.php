<?php

namespace App\Http\Resources;

use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Wallet|JsonResource $this */

        return [
            'id' => $this->id,
            'uuid' => (string) $this->uuid,
            'user_id' => (int) $this->user_id,
            'price' => (double) $this->price,
            'symbol' => (string) $this->symbol,
            'code' => (string) $this->code,
            'created_at' => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at' => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),
            'deleted_at' => $this->when($this->deleted_at, optional($this->deleted_at)->format('Y-m-d H:i:s')),

            // Relations
            'histories' => WalletHistoryResource::collection($this->whenLoaded('histories')),
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
        ];
    }
}
