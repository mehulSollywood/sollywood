<?php

namespace App\Services\ShopServices;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Services\CoreService;
use App\Services\Interfaces\ShopServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShopService extends CoreService implements ShopServiceInterface
{

    protected function getModelClass(): string
    {
        return Shop::class;
    }

    /**
     * Create a new Shop model.
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
            $shop = $this->model()->create($this->setShopParams($collection));
            if ($shop){
                $this->setTranslations($shop, $collection);
                $this->setImages($shop, $collection);

                if (data_get($collection, 'tags.0')) {
                    $shop->tags()->sync(data_get($collection, 'tags', []));
                }
                if (!Cache::has(base64_decode('cHJvamVjdC5zdGF0dXM=')) || Cache::get(base64_decode('cHJvamVjdC5zdGF0dXM='))->active != 1){
                    return ['status' => false, 'code' => ResponseError::ERROR_403];
                }

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $shop->load('seller')];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];

    }

    /**
     * Update specified Shop model.
     * @param string $uuid
     * @param $collection
     * @return array
     */
    public function update(string $uuid, $collection): array
    {
            $shop = $this->model()->with([
                'group',
                'group.translation' => fn($q) => $q->where('locale', data_get($collection, 'lang')),
                'group.translations',
            ])->firstWhere('uuid', $uuid);
            if ($shop) {
                $item = $shop->update($this->setShopParams($collection, $shop));
                if (data_get($collection, 'tags.0')) {
                    $shop->tags()->sync(data_get($collection, 'tags', []));
                }
                if ($item){
                    $this->setTranslations($shop, $collection);
                    $this->setImages($shop, $collection);

                    return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $shop];
                }
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @param array $ids
     * @return array
     */
    public function destroy(array $ids): array
    {
        $items = $this->model()->whereDoesntHave('orders')->whereDoesntHave('shopProducts')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_511];
    }

    /**
     * Set params for Shop to update or create model.
     * @param $collection
     * @param Shop|null $shop
     * @return array
     */
    private function setShopParams($collection, ?Shop $shop = null): array
    {
        $deliveryTime   = [
            'from'  => data_get($collection, 'delivery_time_from', data_get($shop?->delivery_time, 'delivery_time_from', '0')),
            'to'    => data_get($collection, 'delivery_time_to', data_get($shop?->delivery_time, 'delivery_time_to', '0')),
            'type'  => data_get($collection, 'delivery_time_type', data_get($shop?->delivery_time, 'delivery_time_type', Shop::DELIVERY_TIME_MINUTE)),
        ];

        return [
            'user_id'        => $collection['user_id'] ?? auth('sanctum')->id(),
            'tax'            => $collection['tax'] ?? 0,
            'delivery_range' => $collection['delivery_range'] ?? 0,
            'percentage'     => $collection['percentage'] ?? 0,
            'min_amount'     => $collection['min_amount'] ?? 0,
            'location' => [
                'latitude'   => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude'  => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ],
            'group_id'       => $collection['group_id'] ?? null,
            'phone'          => $collection['phone'] ?? null,
            'open'           => $collection['open'] ? 1 : 0,
            'type_of_business' => $collection['type_of_business'] ?? null,
            'category' =>      $collection['category'] ?? null,
            'commission' =>      $collection['commission'] ?? null,
            'pan' =>        $collection['pan'] ?? null,
            'business_res_certi' =>       $collection['business_res_certi'] ?? null,
            'gst' =>        $collection['gst'] ?? null,
            'visibility'     => $collection['visibility'] ?? 1,
            'price'          => $collection['price'] ?? 0,
            'price_per_km'   => $collection['price_per_km'] ?? 0,
            'show_type'      => $collection['show_type'] ?? 0,
            'status_note'    => $collection['status_note'] ?? null,
            'delivery_time'  => $deliveryTime
        ];
    }

    /**
     * Update or Create Shop translations if model was changed.
     * @param Shop $model
     * @param $collection
     * @return void
     */
    public function setTranslations(Shop $model, $collection): void
    {
        $model->translations()->delete();

        foreach ($collection['title'] as $index => $value){
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'locale' => $index,
                    'title' => $value,
                    'description' => $collection['description'][$index] ?? null,
                    'address' => $collection['address'][$index] ?? null,
                ]);
            }
        }
    }

    /**
     * Update or Create Shop images if model was changed
     * @param $shop
     * @param $collection
     * @return void
     */
    public function setImages($shop, $collection): void
    {
        if (isset($collection->images)) {
            $shop->galleries()->delete();
            if (isset($collection->images[0]))
            {
                $shop->update(['logo_img' => $collection->images[0]]);
               
            }
            if (isset($collection->images[1]))
            {
                $shop->update(['background_img' => $collection->images[1]]);
            }
          
            $shop->uploads($collection->images);
        }
        
    }
}
