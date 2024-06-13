<?php

namespace App\Http\Controllers\API\v1\Rest;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CartResource;
use App\Http\Requests\Cart\IndexRequest;
use App\Http\Resources\UserCartResource;
use App\Services\CartService\CartService;
use App\Http\Requests\Cart\OpenCartRequest;
use App\Http\Requests\Cart\GroupStoreRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\CartRepository\CartRepository;

class CartController extends RestBaseController
{

    public function __construct(protected CartRepository $cartRepository,protected CartService $cartService)
    {
        parent::__construct();
    }

    public function get(int $id,IndexRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $cart = $this->cartRepository->get($collection['shop_id'],$id);
        if ($cart){
            return $this->successResponse(__('web.record_was_found'), CartResource::make($cart));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function openCart(OpenCartRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->cartService->openCart($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), UserCartResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function store(GroupStoreRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->cartService->groupCreate($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), CartResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'] , ['quantity' => $result['data'] ?? null], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function userCartDelete(string $user_cart_uuid, Request $request): JsonResponse
    {
        $result = $this->cartService->userCartDelete($user_cart_uuid, $request->cart_id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_delete'));
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function cartProductDelete(int $cart_detail_id): JsonResponse
    {
        $result = $this->cartService->cartProductDelete($cart_detail_id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_delete'));
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function statusChange(string $user_cart_uuid,Request $request): JsonResponse
    {
        $result = $this->cartService->statusChange($user_cart_uuid, $request->cart_id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_status changed'),UserCartResource::make($result['data']) );
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }

    }
}
