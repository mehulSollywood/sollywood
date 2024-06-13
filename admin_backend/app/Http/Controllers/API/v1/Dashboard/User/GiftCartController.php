<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\UserGiftCart;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\UserGiftCartResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GiftCartController extends Controller
{
    public function myGiftCarts(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();

        $userGiftCarts = UserGiftCart::with([
            'shopProduct.product.translation:id,product_id,locale,title,description',
            'shopProduct.product.category:id,uuid',
            'shopProduct.product.category.translation:id,category_id,locale,title',
            'shopProduct.product.brand:id,uuid,title',
            'shopProduct.product.unit.translation',
            'shopProduct.shop.translation',
            'shopProduct.discount',
        ])->where('user_id', auth('sanctum')->user()->id)
            ->orderBy('id', 'desc')
            ->paginate($collection['perPage']);

        return UserGiftCartResource::collection($userGiftCarts);
    }
}
