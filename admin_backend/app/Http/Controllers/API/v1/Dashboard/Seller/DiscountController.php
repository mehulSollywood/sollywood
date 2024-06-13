<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Discount;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Resources\DiscountResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\DiscountService\DiscountService;
use App\Http\Requests\Seller\Discount\StoreRequest;
use App\Http\Requests\Seller\Discount\UpdateRequest;
use App\Repositories\DiscountRepository\DiscountRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiscountController extends SellerBaseController
{

    public function __construct(protected DiscountRepository $discountRepository,protected DiscountService $discountService)
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
        $discounts = $this->discountRepository->discountsPaginate(
            $request->perPage ?? 15, $this->shop->id, $request->active ?? null, $request->all()
        );
        return DiscountResource::collection($discounts);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();
        if ($this->shop) {
            $collection['shop_id'] = $this->shop->id;
            $result = $this->discountService->create($collection);
            if ($result['status']) {
                return $this->successResponse(__('web.record_successfully_created'), DiscountResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        if ($this->shop) {
            $discount = $this->discountRepository->discountDetails($id, $this->shop->id);
            if ($discount) {
                return $this->successResponse(__('web.discount_found'), DiscountResource::make($discount));
            }
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $collection = $request->validated();
        if (!$this->shop) {
            return $this->errorResponse(
                ResponseError::ERROR_101, __('errors.' . ResponseError::ERROR_101, [], $this->language),
                Response::HTTP_BAD_REQUEST
            );
        }

        $collection['shop_id'] = $this->shop->id;
        $result = $this->discountService->update($id, $collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), DiscountResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->discountService->delete($collection['ids']);
        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );

    }

    public function setActiveStatus($id): JsonResponse
    {
        $discount = Discount::firstWhere(['id' => $id, 'shop_id' => $this->shop->id]);
        if ($discount) {
            $discount->update(['active' => !$discount->active]);
            return $this->successResponse(__('web.record_active_update'), DiscountResource::make($discount));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
