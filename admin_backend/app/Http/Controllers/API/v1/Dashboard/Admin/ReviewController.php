<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Review;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ReviewResource;
use App\Http\Requests\DeleteAllRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends AdminBaseController
{

    public function __construct(protected Review $model)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $reviews = $this->model->with(['reviewable', 'user'])
            ->when(isset($request->type) && $request->type == 'order', function ($q) {
                $q->whereHasMorph('reviewable', Order::class);
            })
            ->when(isset($request->type) && $request->type == 'product', function ($q) {
                $q->whereHasMorph('reviewable', ShopProduct::class);
            })
            ->when(isset($request->type) && $request->type == 'deliveryman', function ($q) {
                $q->whereHasMorph('reviewable', User::class);
            })
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return ReviewResource::collection($reviews);
    }

    public function show(int $id): JsonResponse
    {
        $review = $this->model->with(['reviewable', 'galleries', 'user'])->find($id);
        if ($review) {
            return $this->successResponse(__('web.review_found'), ReviewResource::make($review));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteAllRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $items = $this->model->find($collection['ids']);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

}
