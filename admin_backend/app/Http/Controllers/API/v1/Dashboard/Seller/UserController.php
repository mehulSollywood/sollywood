<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Services\UserServices\UserService;
use App\Http\Resources\UserAddressResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\AuthService\UserVerifyService;
use App\Services\UserServices\UserAddressService;
use App\Repositories\UserRepository\UserRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends SellerBaseController
{

    public function __construct(
        protected User $model,
        protected UserRepository $userRepository,
        protected UserService $userService
    )
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $users = $this->userRepository->usersPaginate($request->perPage ?? 15, $request->all(), true);
        return UserResource::collection($users);
    }

    public function show(string $uuid): JsonResponse
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user) {
            return $this->successResponse(__('web.user_found'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserCreateRequest $request
     * @return JsonResponse
     */
    public function store(UserCreateRequest $request): JsonResponse
    {
        $result = $this->userService->create($request->merge(['role' => 'user']));
        if ($result['status']) {
            (new UserVerifyService())->verifyEmail($result['data']);
            return $this->successResponse(__('web.user_create'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function userAddressCreate($uuid, Request $request): JsonResponse
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user) {
            $result = (new UserAddressService)->create($request->merge(['user_id' => $user->id]));
            if ($result['status']) {
                return $this->successResponse(__('web.user_address_create'), UserAddressResource::make($result['data']));
            }

            return $this->errorResponse(
                ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function shopUsersPaginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $search = $request->search ?? null;

        $users = $this->model->with('roles')
            ->whereHas('invite', function ($q) {
                $q->where(['shop_id' => $this->shop->id, 'status' => Invitation::STATUS['excepted']]);
            })
            ->when(isset($request->search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('firstname', 'LIKE', '%' . $search . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%');
                });
            })
            ->when(isset($request->role), function ($q) use ($request) {
                $q->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            })
            ->when(isset($request->active), function ($q) use ($request) {
                $q->where('active', $request->active);
            })
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return UserResource::collection($users);
    }

    public function shopUserShow(string $uuid): JsonResponse
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user && optional($user->invite)->shop_id == $this->shop->id) {
            return $this->successResponse(__('web.user_found'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function getDeliveryman(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $search = $request->search ?? null;
        $users = $this->model->with('roles')
            ->whereHas('roles', function ($q) use ($request) {
                $q->where('name', 'deliveryman');
            })
            ->whereHas('invite', function ($q) {
                $q->where('shop_id', '=', $this->shop->id);
            })
            ->when(isset($request->search), function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('firstname', 'LIKE', '%' . $search . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $search . '%');
                });
            })
            ->whereActive(1)
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return UserResource::collection($users);
    }

    public function setUserActive($uuid): JsonResponse
    {
        $user = $this->userRepository->userByUUID($uuid);
        if ($user && optional($user->invite)->shop_id == $this->shop->id) {
            $user->update(['active' => !$user->active]);

            return $this->successResponse(__('web.user_found'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
