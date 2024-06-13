<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\User;
use App\Models\Order;
use App\Models\Invitation;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Services\UserServices\UserService;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\Deliveryman\BecomeDeliverymanRequest;

class DeliveryManController extends UserBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add Review to OrderDetail ls.
     *
     * @param int $orderId
     * @param Request $request
     * @return JsonResponse
     */
    public function addDeliveryManReview(int $orderId, Request $request): JsonResponse
    {
        $order = Order::where('status',Order::DELIVERED)->find($orderId);

        if ($order && $order->deliveryMan){

            $result = (new UserService())->createReview($order->deliveryman, $request);
            if ($result['status']) {
                return $this->successResponse(ResponseError::NO_ERROR, UserResource::make($result['data']));
            }

        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function becomeDeliveryman(BecomeDeliverymanRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $userId = auth('sanctum')->id();

        $user = User::find($userId);

        if ($user->invite || $user->deliveryManSetting)
        {
            return $this->errorResponse(
                ResponseError::ERROR_113, trans('errors.' . ResponseError::ERROR_113, [], $this->language),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $user->invite()->create([
            'shop_id' => $collection['shop_id'],
            'user_id' => $userId,
            'role'    => User::ROLE_DELIVERYMAN,
            'status'    => Invitation::STATUS_NEW
        ]);

        $user->deliveryManSetting()->create($collection);

        return $this->successResponse(ResponseError::NO_ERROR, UserResource::make($user));
    }
}
