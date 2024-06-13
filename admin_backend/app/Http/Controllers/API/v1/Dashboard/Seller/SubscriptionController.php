<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Subscription;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SubscriptionResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionService\SubscriptionService;

class SubscriptionController extends SellerBaseController
{

    public function __construct(protected Subscription $model,protected SubscriptionService $subscriptionService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $subscriptions = $this->model->subscriptionsList()->where('active', 1);
        return $this->successResponse(trans('web.subscription_list', [], $this->language), SubscriptionResource::collection($subscriptions));
    }

    public function subscriptionAttach(int $id): JsonResponse
    {
        $subscription = Subscription::find($id);
        if (!$subscription) {
            return $this->errorResponse(
                ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }
        return $this->successResponse(
            __('web.subscription_attached'),
            $this->subscriptionService->subscriptionAttach($subscription, $this->shop->id)
        );
    }

}
