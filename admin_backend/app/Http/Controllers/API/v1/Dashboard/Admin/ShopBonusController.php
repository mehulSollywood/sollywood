<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\BonusShop;
use App\Http\Controllers\Controller;
use App\Http\Resources\BonusShopResource;
use App\Http\Requests\FilterParamsRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopBonusController extends Controller
{
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $shopBonuses = BonusShop::with(['shopProduct.product.translation','shop.translation'])
            ->paginate($request->perPage ?? 10);

        return BonusShopResource::collection($shopBonuses);
    }
}
