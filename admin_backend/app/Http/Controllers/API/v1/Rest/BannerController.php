<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Banner;
use App\Models\ShopProduct;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BannerResource;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ShopProductResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\BannerRepository\BannerRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BannerController extends RestBaseController
{

    public function __construct(protected BannerRepository $bannerRepository,protected Banner $model)
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
        $banners = $this->bannerRepository->bannersPaginateRest($request->perPage ?? 15, $request->all());
        return BannerResource::collection($banners);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $banner = $this->bannerRepository->bannerDetails($id);
        if ($banner){
            return $this->successResponse(__('web.banner_found'), BannerResource::make($banner));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }


    /**
     * Banner Products show .
     *
     * @param  int  $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function bannerProducts(int $id): JsonResponse|AnonymousResourceCollection
    {
        $banner = $this->bannerRepository->bannerDetails($id);
        if ($banner){
            $products = ShopProduct::with([
                'product.category.translation' => fn($q) => $q->select('id', 'category_id', 'locale', 'title'),
                'product.brand' => fn($q) => $q->select('id', 'uuid', 'title'),
                'product.unit.translation',
                'product.translation'
            ])
                ->whereHas('product.translation')
                ->whereIn('id', $banner->products)
                ->paginate(15);

            return ShopProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
