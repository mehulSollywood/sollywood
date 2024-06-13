<?php

namespace App\Services\DeliveryZoneService;

use App\Helpers\ResponseError;
use App\Models\DeliveryZone;
use App\Services\CoreService;
use Exception;
use Illuminate\Support\LazyCollection;

class DeliveryZoneService extends CoreService
{

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return DeliveryZone::class;
    }

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function create(array $data): array
    {
        // Если нужна строгая типизация можно включить
        //$data['address'] = $this->latLongToDouble($data);

        $exist = DeliveryZone::where('shop_id', data_get($data, 'shop_id'))->first();

        if (!empty($exist)) {
            return $this->update($exist, $data);
        }

        $deliveryZone = $this->model()->create($data);

        cache()->forget('delivery-zone-list');

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryZone];
    }

    /**
     * @param DeliveryZone $deliveryZone
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function update(DeliveryZone $deliveryZone, array $data): array
    {
        // Если нужна строгая типизация можно включить
        //$data['address'] = $this->latLongToDouble($data);

        $deliveryZone->update($data);

        cache()->forget('delivery-zone-list');

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $deliveryZone];
    }

    /**
     * @param array|null $ids
     * @param int|null $shopId
     * @return array
     * @throws Exception
     */
    public function delete(?array $ids = [], ?int $shopId = null): array
    {
        $deliveryZones = DeliveryZone::whereIn('id', is_array($ids) ? $ids : [])
            ->when($shopId, fn($q) => $q->where('shop_id', $shopId))
            ->get();

        foreach ($deliveryZones as $deliveryZone) {
            $deliveryZone->delete();
        }

        cache()->forget('delivery-zone-list');

        return [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
        ];
    }

}
