<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Shop;
use App\Helpers\Utility;
use App\Models\Currency;
use App\Traits\Loggable;
use App\Traits\SetCurrency;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeliveryZone\DistanceRequest;
use App\Http\Requests\DeliveryZone\CheckDistanceRequest;

class DeliveryZoneController extends RestBaseController
{
    use SetCurrency, Loggable;

    /**
     * @param int $shopId
     * @return array
     */
    public function getByShopId(int $shopId): array
    {
        /** @var DeliveryZone $deliveryZone */

        $deliveryZone = DeliveryZone::where('shop_id', $shopId)->firstOrFail();

        if (!$deliveryZone) {
            return [
                'status' => false,
                'code' => ResponseError::ERROR_404,
            ];
        }

        return [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
            'data' => $deliveryZone,
        ];
    }

    public function deliveryCalculatePrice(int $deliveryId, Request $request): float|JsonResponse
    {
        /** @var DeliveryZone $deliveryZone */

        $deliveryZone = DeliveryZone::find($deliveryId);

        if (!$deliveryZone) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $km = $request->input('km');

        if ($km <= 0) {
            $km = 1;
        }

        return round(
            ($deliveryZone->shop->price + ($deliveryZone->shop->price_per_km * $km)) * $this->currency(), 2
        );
    }

    /**
     * @param DistanceRequest $request
     * @return array
     */
    public function distance(DistanceRequest $request): array
    {
        return [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
            'data' => (new Utility)->getDistance($request->input('origin'), $request->input('destination')),
        ];
    }

    /**
     * @param CheckDistanceRequest $request
     * @return JsonResponse
     */
    public function checkDistance(CheckDistanceRequest $request): JsonResponse
    {

        $shops = Shop::with('deliveryZone:id,shop_id,address')
            ->where([
                ['status', 'approved'],
            ])
            ->whereHas('deliveryZone')
            ->select(['id', 'open', 'status'])
            ->get();

        foreach ($shops as $shop) {

            /** @var Shop $shop */
            $address = optional($shop->deliveryZone)->address;

            if (!is_array($address) || count($address) === 0) {
                continue;
            }

            $check = Utility::pointInPolygon($request->input('address'), $shop->deliveryZone->address);

            if ($check) {
                return $this->successResponse('success', 'success');
            }

        }

        return $this->onErrorResponse(['code' => ResponseError::ERROR_400]);
    }

    /**
     * @param int $id
     * @param CheckDistanceRequest $request
     * @return JsonResponse
     */
    public function checkDistanceByShop(int $id, CheckDistanceRequest $request): JsonResponse
    {
        $collection = $request->validated();
        /** @var Shop $shop */
        $shop = Shop::with('deliveryZone:id,shop_id,address')->whereHas('deliveryZone')
            ->where([
                ['status', 'approved'],
            ])
            ->select(['id', 'open', 'status','location','price_per_km'])
            ->find($id);


        if (empty($shop) || empty($shop->deliveryZone)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404, 'message' => 'empty shop or delivery zone']);
        }

        $check = Utility::pointInPolygon($request->input('address'), $shop->deliveryZone->address);

        if ($check) {

            $rate = Currency::where('id', request('currency_id'))
                ->orWhere('default', 1)
                ->first()
                ->rate;

            $helper      = new Utility;

            $km          = $helper->getDistance($shop->location, data_get($collection, 'address', []));

            $deliveryFee = $helper->getPriceByDistance($km, $shop, $rate);


            return $this->successResponse('success',['delivery_fee' => $deliveryFee]);
        }

        return $this->onErrorResponse(['code' => ResponseError::ERROR_400, 'message' => 'not in polygon']);
    }
}
