<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CurrencyResource;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Interfaces\CurrencyServiceInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CurrencyController extends AdminBaseController
{

    public function __construct(protected Currency $model,protected CurrencyServiceInterface $currencyService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $currencies = $this->model->currenciesList();
        return $this->successResponse(__('web.list_of_currencies'), CurrencyResource::collection($currencies));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->currencyService->create($request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), CurrencyResource::make($result['data']));
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
        $currency = $this->model->find($id);
        if ($currency) {
            return $this->successResponse(__('web.currency_found'), CurrencyResource::make($currency));
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
        $result = $this->currencyService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), CurrencyResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->currencyService->destroy($id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], $this->language),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Get Currency where "default = 1".
     *
     * @return JsonResponse
     */
    public function getDefaultCurrency(): JsonResponse
    {
        $currency = $this->model->whereDefault(1)->first();
        if ($currency) {
            return $this->successResponse(__('web.currency_found'), CurrencyResource::make($currency));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Get all Active languages
     * @return JsonResponse
     */
    public function getActiveCurrencies(): JsonResponse
    {
        $languages = $this->model->whereActive(1)->get();
        return $this->successResponse(__('web.list_of_active_currencies'), CurrencyResource::collection($languages));
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(int $id): JsonResponse|AnonymousResourceCollection
    {
        $currency = $this->model->find($id);
        if ($currency) {
            $currency->update(['active' => !$currency->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), CurrencyResource::make($currency));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
