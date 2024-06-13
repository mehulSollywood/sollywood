<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\TranslationTableResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TranslationController extends AdminBaseController
{

    public function __construct(protected Translation $model)
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
        $translations = $this->model->filter($request->all())->get();
        return TranslationTableResource::collection($translations);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function paginate(FilterParamsRequest $request): JsonResponse
    {
        $perPage = $request->perPage ?? 15;
        $skip = $request->skip ?? 0;

        $translations = $this->model->filter($request->all())
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->get();

        $values = $translations->mapToGroups(function ($item){
            return [
                $item->key => [
                    'id' => $item->id,
                    'group' => $item->group,
                    'locale' => $item->locale,
                    'value' => $item->value,
                ]
            ];
        });

        $count = $values->count();
        $values = $values->skip($skip)->take($perPage);

        return $this->successResponse('errors.' . ResponseError::NO_ERROR, [
            'total' => $count,
            'perPage' => (int) $perPage,
            'translations' => $values
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $key = $this->model->where('key', $request->key)->first();
        if (!$key){
            try {
                foreach ($request->value as $index => $item) {
                    $this->model->create([
                        'group' => $request->group,
                        'key' => $request->key,
                        'locale' => $index,
                        'status' => $request->status ?? 1,
                        'value' => $item,
                    ]);
                    cache()->forget('language-' . $index);
                }

                return $this->successResponse(__('web.translation_created'), []);
            } catch (Exception $exception) {
                return $this->errorResponse(
                    ResponseError::ERROR_404, $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_506, trans('errors.' .ResponseError::ERROR_506, [], $this->language),
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
        $translation = $this->model->find($id);
        if ($translation) {
            return $this->successResponse(__('web.translation_found'), TranslationTableResource::make($translation));
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
     * @param $key
     * @return JsonResponse
     */
    public function update(Request $request, $key): JsonResponse
    {
        $translations = $this->model->where('key', $key)->get();
        if (count($translations) > 0){
            try {
                $this->model->where('key', $key)->delete();
                foreach ($request->value as $index => $item) {
                    $this->model->create([
                        'group' => $request->group,
                        'key' => $key,
                        'locale' => $index,
                        'value' => $item,
                    ]);
                    cache()->forget('language-' . $index);
                }

                return $this->successResponse(trans('errors.'. ResponseError::NO_ERROR), []);
            } catch (Exception $exception) {
                return $this->errorResponse(
                    ResponseError::ERROR_400, $exception->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' .ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
