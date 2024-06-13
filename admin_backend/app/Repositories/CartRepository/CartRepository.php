<?php

namespace App\Repositories\CartRepository;


use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\ShopProduct;
use App\Repositories\CoreRepository;

class CartRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Cart::class;
    }

    public function get($shop_id, $cart_id = null)
    {
        $user_id = auth('sanctum')->user()->id ?? null;

        $query = $this->model()->where('shop_id', $shop_id);

        $cartDetailIds = [];

        if ($cart_id) {
            $query = $query->find($cart_id);
        } else {
            $query = $query->where('owner_id', $user_id)->first();
        }

        if ($query) {
            foreach ($query->userCarts as $userCart) {

                foreach ($userCart->cartDetails as $cartDetail) {

                    /**
                     * variables для автокоплита
                     * @var CartDetail $cartDetail
                     * @var ShopProduct $shopProduct
                     */

                    $outOfQuantityProduct = ShopProduct::where('quantity', '<', 1)
                        ->find($cartDetail->shop_product_id);

                    if ($outOfQuantityProduct) {
                        $cartDetail->delete();
                    }

                    if ($cartDetail->bonus) {

                        $outOfQuantityBonusProduct = $cartDetail?->productBonus->shopProduct
                            ->where('quantity', '<', 1)
                            ->first();

                        if ($outOfQuantityBonusProduct) {
                            $cartDetail->delete();
                        }
                    }

                    if ($cartDetail && !$cartDetail->bonus) {
                        $shopProduct = ShopProduct::find($cartDetail->shop_product_id);
                        if ($shopProduct){
                            $cartDetail->update(['price' => $shopProduct->price * $cartDetail->quantity]);
                        }else{
                            $cartDetail->delete();
                        }
                    }

                    $cartDetailIds[] = $cartDetail?->id;
                }
            }
            $totalPrice = CartDetail::whereIn('id', $cartDetailIds)->sum('price');

            $query->update(['total_price' => $totalPrice]);

            return Cart::with([
                'userCarts.cartDetails.shopProduct.product.unit.translation',
                'userCarts.cartDetails.shopProduct.product',
                'userCarts.cartDetails.shopProduct.product.translation'
            ])->find($query->id);
        }else{
            if ($cart_id) {
                return $this->model()->where('shop_id', $shop_id)->find($cart_id);
            } else {
                return $this->model()->where('shop_id', $shop_id)->where('owner_id', $user_id)->first();
            }
        }
    }

    public function getById(int $id)
    {
        return $this->model()->find($id);
    }

}
