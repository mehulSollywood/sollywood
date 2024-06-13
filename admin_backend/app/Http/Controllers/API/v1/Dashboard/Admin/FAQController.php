<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\Faq;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FAQResource;
use App\Http\Requests\FaqSetRequest;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FAQController extends AdminBaseController
{

    public function __construct(protected Faq $model)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $faqs = $this->model->with(
            'translation'
        )
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return FAQResource::collection($faqs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param FaqSetRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function store(FaqSetRequest $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $faq = $this->model->create([
                'uuid' => Str::uuid(),
                'type' => 'web',
            ]);
            if ($faq && isset($request->question)) {
                foreach ($request->question as $index => $item) {
                    if (isset($item) || $item != '') {
                        $faq->translations()->create([
                            'locale' => $index,
                            'question' => $item,
                            'answer' => $request->answer[$index] ?? null,
                        ]);
                    }
                }
            }
            return $this->successResponse(trans('web.record_successfully_created', [], $this->language), FAQResource::make($faq));
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400,
                $exception->getMessage(),
                Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $faq = $this->model->with([
            'translations',
            'translation'
        ])->firstWhere('uuid', $uuid);

        if ($faq) {
            return $this->successResponse(trans('web.faq_found', [], $this->language), FAQResource::make($faq));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language), Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $uuid
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function update(Request $request, string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $faq = $this->model->firstWhere('uuid', $uuid);
        if ($faq) {
            try {
                $faq->update(['type' => 'web']);
                if (isset($request->question)) {
                    $faq->translations()->delete();
                    foreach ($request->question as $index => $item) {
                        if (isset($item) || $item != '') {
                            $faq->translations()->create([
                                'locale' => $index,
                                'question' => $item,
                                'answer' => $request->answer[$index] ?? null,
                            ]);
                        }
                    }
                }
                return $this->successResponse(trans('web.record_successfully_updated', [], $this->language), FAQResource::make($faq));
            } catch (Exception $exception) {
                return $this->errorResponse(ResponseError::ERROR_400,
                    $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST);
            }
        } else {
            return $this->errorResponse(ResponseError::ERROR_404,
                trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $faq = $this->model->firstWhere('uuid', $uuid);
        if ($faq) {
            try {
                $faq->delete();
                return $this->successResponse(trans('web.record_successfully_deleted', [], $this->language), []);
            } catch (Exception $exception) {
                return $this->errorResponse(ResponseError::ERROR_400,
                    $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST);
            }
        } else {
            return $this->errorResponse(ResponseError::ERROR_404,
                trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND);
        }
    }

    public function setActiveStatus(string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $faq = $this->model->firstWhere('uuid', $uuid);
        if ($faq) {
            $faq->update(['active' => !$faq->active]);
            return $this->successResponse(__('web.record_active_update'), FAQResource::make($faq));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, __('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
