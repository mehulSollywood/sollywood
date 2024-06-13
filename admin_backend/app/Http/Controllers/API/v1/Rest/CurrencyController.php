<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Currency;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CurrencyResource;
use Symfony\Component\HttpFoundation\Response;

class CurrencyController extends RestBaseController
{

    public function __construct(protected Currency $model)
    {
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        $currencies = $this->model->where('active',1)->orderByDesc('default')->get();
        if ($currencies->count() > 0)
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), CurrencyResource::collection($currencies));
        else
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
    }
}
