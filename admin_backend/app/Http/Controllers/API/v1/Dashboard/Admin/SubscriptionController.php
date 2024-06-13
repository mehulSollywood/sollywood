<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use App\Models\EmailSubscription;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SubscriptionResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\EmailSubscriptionResource;
use App\Services\SubscriptionService\SubscriptionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionController extends AdminBaseController
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
        $subscriptions = $this->model->subscriptionsList();
        return $this->successResponse(trans('web.subscription_list', [], $this->language), SubscriptionResource::collection($subscriptions));
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $subscription = $this->model->find($id);
        if ($subscription) {
            return $this->successResponse(trans('web.subscription_list', [], $this->language), SubscriptionResource::make($subscription));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.ERROR_404', [], $this->language), Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->subscriptionService->update($id, $request);
        if ($result['status']) {

            cache()->forget('subscriptions-list');
            return $this->successResponse(trans('web.subscription_list', [], $this->language), SubscriptionResource::make($result['data']));
        }
        return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language), Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function emailSubscriptions(Request $request): AnonymousResourceCollection
    {
        $emailSubscriptions = EmailSubscription::with([
            'user' => fn($q) => $q->select([
                'id',
                'uuid',
                'firstname',
                'lastname',
                'email',
            ])
        ])
            ->when($request->input('user_id'), fn($q, $userId) => $q->where('user_id', $userId))
            ->paginate($request->input('perPage', 10));

        return EmailSubscriptionResource::collection($emailSubscriptions);
    }
}
