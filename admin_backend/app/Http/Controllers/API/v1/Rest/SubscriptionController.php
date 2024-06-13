<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\Subscription\SubscriptionRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends RestBaseController
{
    public function subscription(SubscriptionRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $user = User::updateOrCreate([
            'email' => $collection['email']
        ],[
            'email' => $collection['email']
        ]);

        if ($user)
        {
            return $this->successResponse(__('web.user_create'), UserResource::make($user));
        }

        return $this->errorResponse(
            ResponseError::ERROR_501, trans('errors.' . ResponseError::ERROR_501, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }
}
