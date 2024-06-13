<?php

namespace App\Http\Resources;

use App\Models\Banner;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Banner|JsonResource $this */

        return [
            'id'           => (int) $this->id,
            'shop_id'      => (int) $this->shop_id,
            'url'          => (string) $this->url,
            'products'     => (array) $this->products,
            'img'          => (string) $this->img,
            'active'       => (boolean) $this->active,
            'created_at'   => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at'   =>  $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'translation'  => TranslationResource::make($this->whenLoaded('translation')),
            'translations' => TranslationResource::collection($this->whenLoaded('translations')),
            'shop'         => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
