<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Delivery;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DeliveryResource;
use App\Http\Requests\DeliveryCreateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\DeliveryService\DeliveryService;
use App\Repositories\DeliveryRepository\DeliveryRepository;

class DeliveryController extends SellerBaseController
{

    public function __construct(protected DeliveryRepository $deliveryRepository,protected DeliveryService $deliveryService)
    {
        parent::__construct();
    }


    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $deliveries = $this->deliveryRepository->deliveriesList($this->shop->id, $request->active ?? null, $request->all());
        return $this->successResponse(trans('web.deliveries_list', [], $this->language), DeliveryResource::collection($deliveries));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DeliveryCreateRequest $request
     * @return JsonResponse
     */
    public function store(DeliveryCreateRequest $request): JsonResponse
    {
        $result = $this->deliveryService->create($request->merge(['shop_id' => $this->shop->id]));
        if ($result['status']) {
            return $this->successResponse(trans('web.record_successfully_created', [], $this->language), DeliveryResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $delivery = $this->deliveryRepository->deliveryDetails($id, $this->shop->id);
        if ($delivery) {
            $delivery->load('translations');

            return $this->successResponse(trans('web.delivery_found', [], $this->language), DeliveryResource::make($delivery));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DeliveryCreateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(DeliveryCreateRequest $request, int $id): JsonResponse
    {
        $delivery = Delivery::where('shop_id', $this->shop->id)->find($id);
        if ($delivery) {
            $result = $this->deliveryService->update($id, $request->merge(['shop_id' => $this->shop->id]));
            if ($result['status']) {
                return $this->successResponse(trans('web.record_successfully_updated', [], $this->language), DeliveryResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Get Delivery types.
     *
     * @return JsonResponse
     */
    public function deliveryTypes(): JsonResponse
    {
        return $this->successResponse(trans('web.delivery_types_list', [], $this->language), Delivery::TYPES);
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function setActive(int $id): JsonResponse
    {
        $delivery = Delivery::where('shop_id', $this->shop->id)->find($id);
        if ($delivery) {
            $delivery->update(['active' => !$delivery->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), DeliveryResource::make($delivery));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
