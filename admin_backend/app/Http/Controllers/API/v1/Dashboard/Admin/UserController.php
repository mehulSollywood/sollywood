<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Resources\WalletHistoryResource;
use App\Http\Requests\User\User\UpdateRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Services\AuthService\UserVerifyService;
use App\Services\UserServices\UserWalletService;
use App\Services\Interfaces\UserServiceInterface;
use App\Repositories\Interfaces\UserRepoInterface;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends AdminBaseController
{

    public function __construct(protected UserServiceInterface $userService,protected UserRepoInterface $userRepository)
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
        $users = $this->userRepository->usersPaginate($request->perPage ?? 15, $request->all(), $request->active);
        return UserResource::collection($users);
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
        if ($result['status']){
            (new UserVerifyService())->verifyEmail($result['data']);

            return $this->successResponse(__('web.user_create'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $user = $this->userRepository->userByUUID($uuid);
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
     * @param UpdateRequest $request
     * @param string $uuid
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, string $uuid): JsonResponse
    {
        $result = $this->userService->update($uuid, $request);
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
     * @param DeleteAllRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function deleteAll(DeleteAllRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();

        $result = $this->userService->destroy($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function destroy($id): JsonResponse
    {
        $result = $this->userService->deleteWorkers($id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function updateRole($uuid, Request $request): JsonResponse
    {

        try {
            $user = $this->userRepository->userByUUID($uuid);
            if ($user){
                if (isset($user->shop) && $user->shop->status == 'approved' || $user->role == 'seller' || $request->role == 'seller') {
                    return $this->errorResponse(ResponseError::ERROR_110, __('errors.' . ResponseError::ERROR_110), Response::HTTP_BAD_REQUEST);
                }
                $user->syncRoles([$request->role]);
                return $this->successResponse(__('web.record_successfully_updated'), UserResource::make($user));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e){
            return $this->errorResponse(ResponseError::ERROR_400, $e->getMessage(),Response::HTTP_BAD_REQUEST);
        }
    }

    public function usersSearch(Request $request): AnonymousResourceCollection
    {
        $users = $this->userRepository->usersSearch($request->search ?? '', true, $request->roles ?? []);
        return UserResource::collection($users);
    }

    public function setActive(string $uuid): JsonResponse
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user) {
            $user->active = !$user->active;
            $user->save();

            return $this->successResponse(__('web.record_has_been_successfully_updated'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Top up User Wallet by UUID
     *
     * @param string $uuid
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function topUpWallet(string $uuid, FilterParamsRequest $request): JsonResponse
    {
        $user = User::firstWhere('uuid', $uuid);
        if ($user) {
            $result = (new UserWalletService())->update($user, ['price' => $request->price, 'note' => $request->note]);
            if ($result['status']) {
                return $this->successResponse(__('web.walled_has_been_updated'), UserResource::make($user));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Get User Wallet History by UUID
     *
     * @param string $uuid
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function walletHistories(string $uuid, FilterParamsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $user = User::firstWhere('uuid', $uuid);

        if ($user) {

            $data = $request->validated();
            $data['wallet_uuid'] = data_get($user, 'wallet.uuid');

            $histories = (new WalletHistoryRepository)->walletHistoryByUuIdPaginate($request->perPage ?? 15, $data);
            return WalletHistoryResource::collection($histories);
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Get User Wallet History by UUID
     *
     * @param string $uuid
     * @param PasswordUpdateRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function passwordUpdate(string $uuid, PasswordUpdateRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->userService->updatePassword($uuid,$request->validated());
        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }
}
