<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Faq;
use App\Models\PrivacyPolicy;
use App\Models\TermCondition;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FAQResource;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FAQController extends RestBaseController
{
    public function __construct(protected Faq $model)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the FAQ.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $faqs = $this->model->with(
            'translation')
            ->where('active', 1)
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return FAQResource::collection($faqs);
    }

    /**
     * Display Terms & Condition.
     *
     * @return JsonResponse
     */
    public function term(): JsonResponse
    {
        $model = TermCondition::with('translation')->first();
        if ($model) {
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Display Terms & Condition.
     *
     * @return JsonResponse
     */
    public function policy(): JsonResponse
    {
        $model = PrivacyPolicy::with('translation')->first();
        if ($model) {
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
