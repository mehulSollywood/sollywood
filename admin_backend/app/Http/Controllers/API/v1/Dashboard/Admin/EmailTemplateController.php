<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\EmailTemplateResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\EmailTemplate\StoreRequest;
use App\Services\EmailTemplateService\EmailTemplateService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\EmailTemplateRepository\EmailTemplateRepository;

class EmailTemplateController extends AdminBaseController
{

    public function __construct(protected EmailTemplateService $service,protected EmailTemplateRepository $repository)
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
        return EmailTemplateResource::collection($this->repository->paginate($request->all()));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $result = $this->service->create($request->validated());

        if (!data_get($result, 'status')) {
            return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(__('web.record_successfully_created'), []);
    }

    /**
     * Display the specified resource.
     *
     * @param EmailTemplate $emailTemplate
     * @param Request $request
     * @return JsonResponse
     */
    public function show(EmailTemplate $emailTemplate, Request $request): JsonResponse
    {
        $show = $this->repository->show($emailTemplate, $request->all());

        return $this->successResponse(
            trans('web.subscription_list', [], request('lang')),
            EmailTemplateResource::make($show)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EmailTemplate $emailTemplate
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(EmailTemplate $emailTemplate, StoreRequest $request): JsonResponse
    {
        $result = $this->service->update($emailTemplate, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        return $this->successResponse(__('web.email_template_types_found'), EmailTemplate::TYPES);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */

    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->destroy($request->input('ids', []));

        if (!$result['status']){
            return $this->errorResponse($result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
                Response::HTTP_BAD_REQUEST);
        }
        return $this->successResponse(__('web.record_successfully_deleted'), []);
    }
}
