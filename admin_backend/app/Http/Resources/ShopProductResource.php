<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ShopProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ShopProduct $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'min_qty' => $this->min_qty,
            'max_qty' => $this->max_qty,
            'active' => $this->active,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'tax' => round($this->tax,2),
            'shop_id' => $this->shop_id,
            'orders_count' => $this->orders_count,
            'orders_week_count' => $this->orders_week_count,
            'rating_avg' => $this->reviews_avg_rating,
            'review' => $this->reviewAble(),
            'reviews_count' => $this->when($this->reviews_count, $this->reviews_count),
            'bonus' => BonusProductResource::make($this->whenLoaded('bonus')),
            $this->mergeWhen($this->reviews_count > 0, [
                'rating_percent' => $this->ratingPercent()
            ]),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'discount' => $this->when($this->discount, (double) $this->actualDiscount),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'shop' => ShopResource::make($this->whenLoaded('shop'))
        ];
    }

    public function ratingPercent(): Collection
    {
        $reviews = $this->reviews()->select('rating')->get();

        $reviews = collect($reviews)->mapToGroups(function ($item) use($reviews) {
            $rating = $reviews->where('rating', $item->rating)->count();

            return [ $item->rating => ($rating * 100) / $reviews->count() ];
        });

        // Loop the collection through map and sum the amount of each group
        return $reviews->map(function ($item){
            return $item->pipe(function ($value){
                return collect($value)->unique()[0];
            });
        });
    }

    protected function reviewAble(): bool
    {
        if (auth('sanctum')->user() && request('review')) {
            $orderProduct = Order::whereHas('orderDetails', function ($query){
                $query->where('shop_product_id', $this->id);
            })
                ->where('status',Order::DELIVERED)
                ->where('user_id', auth('sanctum')->user()->id)
                ->first();

            if ($orderProduct){
                return true;
            }
            return false;
        }
        return false;
    }
}
