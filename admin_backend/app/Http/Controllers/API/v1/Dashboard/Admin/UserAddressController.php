<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserAddressResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserServices\UserAddressService;

class UserAddressController extends AdminBaseController
{

    public function __construct(protected UserAddressService $addressService,protected UserAddress $model)
    {
        parent::__construct();
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function store(string $uuid, Request $request): JsonResponse
    {
        $user = User::firstWhere('uuid', $uuid);
        if ($user){
            $result = $this->addressService->create($request->merge(['user_id' => $user->id]));
            if ($result['status']){
                return $this->successResponse(__('web.user_create'), UserAddressResource::make($result['data']));
            }
            return $this->errorResponse(
                ResponseError::ERROR_400, $result['message'] ?? trans('errors.' . ResponseError::ERROR_400, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


}
