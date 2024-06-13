<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Http\Resources\ShopTagResource;
use App\Http\Requests\FilterParamsRequest;
use App\Services\ShopTagService\ShopTagService;
use App\Repositories\ShopTagRepository\ShopTagRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopTagController extends SellerBaseController
{
    public function __construct(protected ShopTagService $service,protected ShopTagRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $shopTag = $this->repository->paginate($request->all());

        return ShopTagResource::collection($shopTag);
    }


}
