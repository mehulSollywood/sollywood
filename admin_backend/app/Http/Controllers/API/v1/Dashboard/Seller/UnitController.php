<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UnitResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\UnitRepository\UnitRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends SellerBaseController
{

    public function __construct(protected UnitRepository $unitRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $units = $this->unitRepository->unitsPaginate($request->perPage ?? 15, $request->active ?? null, $request->all());
        return UnitResource::collection($units);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $unit = $this->unitRepository->unitDetails($id);
        if ($unit){
            return $this->successResponse(__('web.unit_found'), UnitResource::make($unit));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
