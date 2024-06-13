<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ReviewResource;
use App\Repositories\ReviewRepository\ReviewRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends SellerBaseController
{
    /**
     * @param ReviewRepository $repository
     */
    public function __construct(private ReviewRepository $repository)
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $filter = $request->merge(['assign' => 'shop', 'assign_id' => $this->shop->id])->all();

        return ReviewResource::collection($this->repository->paginate($filter));
    }

    /**
     * @param Review $review
     * @return JsonResponse
     */
    public function show(Review $review): JsonResponse
    {
        $review = $this->repository->show($review);

        if (empty($review)) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(__('web.review_found'), ReviewResource::make($review));
    }
}
