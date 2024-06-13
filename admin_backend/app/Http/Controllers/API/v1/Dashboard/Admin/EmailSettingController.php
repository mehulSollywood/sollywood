<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\EmailSetting;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\DeleteAllRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\EmailSettingResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\EmailSetting\StoreRequest;
use App\Services\EmailSettingService\EmailSettingService;
use App\Repositories\EmailSettingRepository\EmailSettingRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmailSettingController extends AdminBaseController
{

    public function __construct(protected EmailSettingService $service,protected EmailSettingRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $emailSettings = $this->repository->get($request->all());

        return EmailSettingResource::collection($emailSettings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->service->create($data);

        if (!data_get($result, 'status')) {
            return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(__('web.record_successfully_created'), []);
    }

    /**
     * Display the specified resource.
     *
     * @param EmailSetting $emailSetting
     * @return JsonResponse
     */
    public function show(EmailSetting $emailSetting): JsonResponse
    {
        return $this->successResponse(
            __('web.coupon_found'),
            EmailSettingResource::make($this->repository->show($emailSetting))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EmailSetting $emailSetting
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(EmailSetting $emailSetting, StoreRequest $request): JsonResponse
    {
        $result = $this->service->update($emailSetting, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
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

        $this->service->delete($collection['ids']);

        return $this->successResponse(__('web.record_has_been_successfully_delete'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function setActive(int $id): JsonResponse
    {
        $this->service->setActive($id);

        return $this->successResponse(__('web.record_has_been_successfully_delete'));
    }
}
