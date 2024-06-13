<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\GroupResource;
use App\Http\Requests\DeleteAllRequest;
use App\Services\GroupService\GroupService;
use App\Http\Requests\Admin\Group\StoreRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\Group\UpdateRequest;
use App\Repositories\GroupRepository\GroupRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GroupController extends AdminBaseController
{

    public function __construct(
        protected GroupService $groupService,
        protected GroupRepository $groupRepository
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->groupRepository->paginate($request->perPage ?? 15);
        return GroupResource::collection($categories);
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
        $result = $this->groupService->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), GroupResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
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
        $category = $this->groupRepository->show($id);
        if ($category){
            return $this->successResponse(__('web.category_found'), GroupResource::make($category));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param string $id
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(string $id, UpdateRequest $request): JsonResponse
    {
        $collection = $request->validated();
        $result = $this->groupService->update($id, $collection);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'),GroupResource::make($result['data']));
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

        $result = $this->groupService->delete($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param integer $id
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function statusChange(int $id): JsonResponse|AnonymousResourceCollection
    {
        $group = $this->groupRepository->show($id);
        if ($group) {
            $group->update(['status' => !$group->status]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), GroupResource::make($group));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
