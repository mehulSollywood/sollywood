<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Product|JsonResource $this */
        return [
            'id' => $this->id,
            'bar_code' => $this->qr_code ?? null,
            'category_id' => $this->category_id ?? null,
            'deleted_at' => $this->deleted_at,
            'quantity' => $this->quantity,
            'count' => $this->count,
            'price' => $this->price,

            // Relations
            'product_title' => $this->translation?->title ?? 'Title deleted',
            'category' => CategoryResource::make($this->category),
        ];

    }

}
