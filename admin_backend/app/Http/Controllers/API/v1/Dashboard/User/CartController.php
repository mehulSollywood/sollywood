<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CartResource;
use App\Http\Resources\UserCartResource;
use App\Http\Requests\Cart\StoreRequest;
use App\Services\CartService\CartService;
use App\Services\OrderService\OrderService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Cart\OpenCartOwnerRequest;
use App\Http\Requests\Cart\InsertProductsRequest;
use App\Repositories\CartRepository\CartRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CartController extends UserBaseController
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected CartService $cartService,
        protected OrderService $orderService
    )
    {
        parent::__construct();
    }

    public function get(Request $request): JsonResponse
    {
        $cart = $this->cartRepository->get($request->shop_id);
        if ($cart) {
            return $this->successResponse(__('web.record_was_found'), CartResource::make($cart));
        }
        return $this->successResponse(__('web.record_was_found'));
    }

    public function store(StoreRequest $request): JsonResponse
    {

        $collection = $request->validated();
        $result = $this->cartService->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), CartResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], ['quantity' => $result['data']['quantity'] ?? null], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function openCart(OpenCartOwnerRequest $request): JsonResponse
    {
        /** @var User $user */

        $collection = $request->validated();
        $user = auth('sanctum')->user();
        $collection['owner_id'] = $user->id;
        $collection['user_id'] = $user->id;
        $collection['name'] = $user->firstname ?? $user->email;
        $collection['together'] = true;
        $result = $this->cartService->openCartOwner($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), CartResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], ['quantity' => $result['data'] ?? null], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function delete(int $id): JsonResponse
    {
        $result = $this->cartService->destroy($id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_delete'));
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
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
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function userCartDelete(string $user_cart_uuid, Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->cartService->userCartDelete($user_cart_uuid, $request->cart_id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_delete'));
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function insertProducts(InsertProductsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();
        $result = $this->cartService->insertProducts($collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'));
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function statusChange(string $user_cart_uuid,Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->cartService->statusChange($user_cart_uuid, $request->cart_id);
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'),UserCartResource::make($result['data']));
        } else {
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function cartCalculate(int $id): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->orderService->orderProductsCalculate($id);
        return $this->successResponse(__('web.products_calculated'), $result);
    }


}
