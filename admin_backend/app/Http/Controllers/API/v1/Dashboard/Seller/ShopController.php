<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Interfaces\ShopServiceInterface;
use App\Repositories\Interfaces\ShopRepoInterface;
use App\Services\ShopServices\ShopActivityService;

class ShopController extends SellerBaseController
{

    public function __construct(protected ShopRepoInterface $shopRepository,protected ShopServiceInterface $shopService)
    {
        parent::__construct();
    }


    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function shopShow(): JsonResponse
    {
        if ($this->shop) {
            $shop = $this->shopRepository->shopDetails($this->shop->uuid);
            if ($shop){
                return $this->successResponse(
                    __('errors.' . ResponseError::NO_ERROR),
                    ShopResource::make($shop->load('translations', 'seller.wallet', 'group'))
                );
            }
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);

        }
        return $this->errorResponse(
            ResponseError::ERROR_204,
            __('errors.' . ResponseError::ERROR_204, [], $this->language ?? 'en'),
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shopUpdate(Request $request): JsonResponse
    {
        if ($this->shop) {
            $result = $this->shopService->update($this->shop->uuid, $request);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], $this->language),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function setVisibilityStatus(): JsonResponse
    {
        if ($this->shop) {
            (new ShopActivityService())->changeVisibility($this->shop->uuid);
            return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($this->shop));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], $this->language),
                Response::HTTP_FORBIDDEN
            );
        }
    }

    public function setWorkingStatus(): JsonResponse
    {
        if ($this->shop) {
            (new ShopActivityService())->changeOpenStatus($this->shop->uuid);
            return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($this->shop));
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_204, __('errors.' . ResponseError::ERROR_204, [], $this->language),
                Response::HTTP_FORBIDDEN
            );
        }
    }


}
