<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\Rest\Delivery\IndexRequest;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\ShopResource;
use App\Models\Delivery;
use App\Repositories\Interfaces\ShopRepoInterface;
use App\Repositories\ShopRepository\ShopDeliveryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends RestBaseController
{
    public function __construct(protected ShopRepoInterface $shopRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $shops = $this->shopRepository->shopsPaginate($request->perPage ?? 15,
            $request->merge([
                'status' => 'approved',
                'visibility' => 1,
                'open' => 1
            ])->all());

        return ShopResource::collection($shops);
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $shop = $this->shopRepository->shopDetails($uuid);
        if ($shop) {
            return $this->successResponse(__('web.shop_found'), ShopResource::make($shop));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search shop Model from database.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function shopsSearch(Request $request): AnonymousResourceCollection
    {
        $shops = $this->shopRepository->shopsSearch($request->search ?? '', [
            'status' => 'approved',
            'visibility' => 1,
            'open' => 1
        ]);
        return ShopResource::collection($shops);
    }

    public function nearbyShops(Request $request): JsonResponse
    {
        $shops = (new ShopDeliveryRepository())->findNearbyShops($request->clientLocation, $request->shopLocation ?? null);
        return $this->successResponse(__('web.list_of_shops'), ShopResource::collection($shops));
    }

    /**
     * Search shop Model from database.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function shopsDeliveryByIDs(Request $request): AnonymousResourceCollection
    {
        $shops = Delivery::with([
            'translation',
        ])->where('shop_id',$request->shops[0])->get();

        return DeliveryResource::collection($shops);
    }

    /**
     * Search shop Model from database via IDs.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function shopsByIDs(Request $request): AnonymousResourceCollection
    {
        $shops = $this->shopRepository->shopsByIDs($request->shops, 'approved');
        return ShopResource::collection($shops);
    }

    public function showById($id): JsonResponse
    {
        $shop = $this->shopRepository->shopById($id);
        if ($shop) {
            return $this->successResponse(__('web.shop_found'), ShopResource::make($shop));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function showBySlug($slug): JsonResponse
    {
        $shop = $this->shopRepository->showBySlug($slug);
        if ($shop) {
            return $this->successResponse(__('web.shop_found'), ShopResource::make($shop));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
