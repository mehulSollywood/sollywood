<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BranchResource;
use App\Http\Requests\FilterParamsRequest;
use App\Services\BranchService\BranchService;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\BranchRepository\BranchRepository;

class BranchController extends UserBaseController
{

    public function __construct(protected BranchRepository $repository,protected BranchService $service)
    {
        parent::__construct();
    }

    public function index(FilterParamsRequest $request): JsonResponse
    {
        $branch = $this->repository->paginate($request->perPage, $request->shop_id);
        return $this->successResponse(__('web.list_of_branch'), BranchResource::collection($branch));
    }

    public function show(int $id): JsonResponse
    {
        $branch = $this->repository->getById($id);
        if ($branch) {
            return $this->successResponse(__('web.branch_found'), BranchResource::make($branch));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
