<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\UserAddressResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserServices\UserAddressService;

class AddressController extends UserBaseController
{

    public function __construct(protected UserAddress $model,protected UserAddressService $addressService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function index(FilterParamsRequest $request): JsonResponse
    {
        $address = $this->model->where('user_id', auth('sanctum')->id())->paginate($request->perPage ?? 15);
        return $this->successResponse(__('web.list_of_address'), UserAddressResource::collection($address));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->addressService->create($request->merge(['user_id' => auth('sanctum')->id()]));
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), UserAddressResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $address = $this->model->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($address) {
            return $this->successResponse(__('web.address_found'), UserAddressResource::make($address));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->addressService->update($id, $request->merge(['user_id' => auth('sanctum')->id()]));
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), UserAddressResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $address = $this->model->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($address) {
            $address->delete();
            return $this->successResponse(__('web.record_has_been_successfully_deleted'), []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Make specific Language as default
     * @param  int  $id
     * @return JsonResponse
     */
    public function setDefaultAddress(int $id): JsonResponse
    {
        $result = $this->addressService->setAddressDefault($id, 1);
        if ($result['status']) {
            return $this->successResponse(__('web.item_is_default_now'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function setActiveAddress(int $id): JsonResponse
    {
        $address = $this->model->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($address) {
            $address->update(['active' => !$address->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), UserAddressResource::make($address));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
