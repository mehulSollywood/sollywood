<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopBrandResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\ShopBrandRepository\ShopBrandRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends RestBaseController
{

    public function __construct(protected ShopBrandRepository $shopBrandRepository)
    {
        parent::__construct();
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth('sanctum')->user();
        if ($user){
            if ($user->role == 'seller'){
                $shop = Shop::where('user_id',$user->id)->first();
                if ($shop){
                    $request->merge(['shop_id' => $shop->id]);
                }
            }
        }
        $brands = $this->shopBrandRepository->paginate($request->perPage ?? 15,$request->merge(['rest' => true])->all());
        return ShopBrandResource::collection($brands);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $brand = $this->shopBrandRepository->show($id);
        if ($brand){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), ShopBrandResource::make($brand));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $brand = $this->shopBrandRepository->showBySlug($slug);
        if ($brand){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), ShopBrandResource::make($brand));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
}
