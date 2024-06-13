<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use DB;
use App\Models\User;
use App\Models\Transaction;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserCreateRequest;
use App\Services\UserServices\UserService;
use App\Http\Resources\NotificationResource;
use App\Http\Requests\PasswordUpdateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\UserRepository\UserRepository;
use App\Http\Requests\PasswordUpdateWithPhoneRequest;
use App\Http\Requests\User\Profile\FireBaseTokenUpdateRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\User\Notification\UserNotificationsRequest;

class ProfileController extends UserBaseController
{
    public function __construct(protected UserRepository $userRepository,protected UserService $userService)
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserCreateRequest $request): JsonResponse
    {
        $result = $this->userService->create($request);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), $request['data']);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();
        $user = $this->userRepository->userById($user->id);
        if ($user) {
            return $this->successResponse(__('web.user_found'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function update(UserCreateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        $result = $this->userService->update($user->uuid, $request);

        if ($result['status']){
            return $this->successResponse(__('web.user_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function delete(): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        $user = $this->userRepository->userByUUID($user->uuid);
        if ($user) {
            $user->delete();
            return $this->successResponse(__('web.record_has_been_successfully_deleted'), []);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
    *
    */
    public function fireBaseTokenUpdate(FireBaseTokenUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        $collection = $request->validated();

        if (empty($collection['firebase_token'])) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_502, 'message' => 'token is empty']);
        }

        $user = User::firstWhere('uuid', $user->uuid);

        if (empty($user)) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $tokens   = is_array($user->firebase_token) ? $user->firebase_token : [$user->firebase_token];
        $tokens[] = $collection['firebase_token'];

        $user->update([
            'firebase_token' => collect($tokens)->reject(fn($item) => empty($item))->unique()->values()->toArray()
        ]);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language)
        );
    }

    public function passworUdpdate(PasswordUpdateRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth('sanctum')->user();

        $collection = $request->validated();

        $result = $this->userService->updatePassword($user->uuid, $collection);
        if ($result['status']){
            return $this->successResponse(__('web.user_password_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
           ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function passwordUpdateWithPhone(PasswordUpdateWithPhoneRequest $request): JsonResponse
    {   
       
        $collection = $request->validated();
     
        $result = $this->userService->updatePasswordWithPhone($collection);
      
        if ($result['status']){
            return $this->successResponse(__('web.user_password_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function notifications(): AnonymousResourceCollection
    {
        return NotificationResource::collection($this->userRepository->usersNotifications());
    }

    public function notificationsUpdate(UserNotificationsRequest $request): JsonResponse
    {
        $result = $this->userService->updateNotifications($request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            UserResource::make(data_get($result, 'data'))
        );
    }

    public function notificationStatistic(): array
    {
        $notification = DB::table('push_notifications')
            ->select([
                DB::raw('count(id) as count'),
                DB::raw("sum(if(type = 'new_order', 1, 0)) as total_new_order_count"),
                DB::raw("sum(if(type = 'new_user_by_referral', 1, 0)) as total_new_user_by_referral_count"),
                DB::raw("sum(if(type = 'status_changed', 1, 0)) as total_status_changed_count"),
                DB::raw("sum(if(type = 'new_in_table', 1, 0)) as total_new_in_table_count"),
                DB::raw("sum(if(type = 'booking_status', 1, 0)) as total_booking_status_count"),
                DB::raw("sum(if(type = 'new_booking', 1, 0)) as total_new_booking_count"),
                DB::raw("sum(if(type = 'news_publish', 1, 0)) as total_news_publish_count"),
            ])
            ->whereNull('read_at')
            ->where('user_id', auth('sanctum')->id())
            ->first();

        $transaction = DB::table('transactions')
            ->select([
                DB::raw('count(id) as count'),

            ])
            ->where('status', Transaction::PROGRESS)
            ->where('user_id', auth('sanctum')->id())
            ->first();

        return [
            'notification'          => (int)$notification?->count,
            'new_order'             => (int)$notification?->total_new_order_count,
            'new_user_by_referral'  => (int)$notification?->total_new_user_by_referral_count,
            'status_changed'        => (int)$notification?->total_status_changed_count,
            'new_in_table'          => (int)$notification?->total_new_in_table_count,
            'booking_status'        => (int)$notification?->total_booking_status_count,
            'new_booking'           => (int)$notification?->total_new_booking_count,
            'news_publish'          => (int)$notification?->total_news_publish_count,
            'transaction'           => (int)$transaction?->count
        ];
    }
}
