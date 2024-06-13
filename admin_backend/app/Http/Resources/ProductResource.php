<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Product|JsonResource $this */

        return [
            'id'            => $this->id,
            'uuid'          => $this->uuid,
            'category_id'   => $this->category_id,
            'keywords'      => $this->keywords,
            'brand_id'      => $this->brand_id,
            'img'           => $this->img ?? null,
            'qr_code'       => $this->qr_code,
            'slug'          => $this->slug,
            'gift'          => $this->gift,
            'created_at'    => $this->when($this->created_at, optional($this->created_at)->format('Y-m-d H:i:s')),
            'updated_at'    => $this->when($this->updated_at, optional($this->updated_at)->format('Y-m-d H:i:s')),

            // Relations
            'translation'   => TranslationResource::make($this->whenLoaded('translation')),
            'translations'  => TranslationResource::collection($this->whenLoaded('translations')),
            'properties'    => ProductPropertyResource::collection($this->whenLoaded('properties')),
            'category'      => CategoryResource::make($this->whenLoaded('category')),
            'brand'         => BrandResource::make($this->whenLoaded('brand')),
            'unit'          => UnitResource::make($this->whenLoaded('unit')),
            'galleries'     => GalleryResource::collection($this->whenLoaded('galleries')),
        ];
    }





}
