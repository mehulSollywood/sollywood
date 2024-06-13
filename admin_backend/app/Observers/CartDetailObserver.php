<?php

namespace App\Observers;

use App\Models\BonusProduct;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\ShopProduct;

class CartDetailObserver
{
    public function updated(CartDetail $cartDetail)
    {
//        $this->addBonusProduct($cartDetail);
    }


    public function created(CartDetail $cartDetail)
    {
//        $this->addBonusProduct($cartDetail);
    }

    public function deleted(CartDetail $cartDetail)
    {
//        $shopBonusProduct = BonusProduct::firstWhere('shop_product_id',$cartDetail->shop_product_id);
//
//        $cartId = $cartDetail->userCart->cart->id;
//
//        $cart = Cart::find($cartId);
//
//        $cart->update(['total_price' => $cart->total_price - $cartDetail->price]);
//
//        if ($shopBonusProduct) {
//            $bonus_product_detail = CartDetail::where('shop_product_id', $shopBonusProduct->bonus_product_id)->first();
//            if ($bonus_product_detail) {
//                $bonus_product_detail->delete();
//            }
//        }
    }

    protected function addBonusProduct($cartDetail)
    {

        /**
         * @var CartDetail $cartDetail
         * @var ShopProduct $shopProductBonus
         */
        $shopProductBonus = BonusProduct::query()->whereHas('bonusProduct', function ($q) {
            $q->where('quantity', '>', 0);
        })
            ->where('shop_product_id', $cartDetail->shop_product_id)
            ->first();

        if($shopProductBonus){
            if ($shopProductBonus->first()->shop_product_quantity <= $cartDetail->quantity) {

                $quantity = floor($cartDetail->quantity / $shopProductBonus->shop_product_quantity) * $shopProductBonus->bonus_quantity;

                CartDetail::updateOrCreate([
                    'shop_product_id' => $shopProductBonus->bonus_product_id,
                    'user_cart_id' => $cartDetail->user_cart_id,
                    'bonus' => true,
                ], [
                    'quantity' => $quantity,
                    'price' => 0
                ]);

            }

            if ($shopProductBonus->first()->shop_product_quantity > $cartDetail->quantity) {
                $cartDetail = CartDetail::where('shop_product_id',$shopProductBonus->bonus_product_id)
                    ->where('bonus',1)
                    ->first();

                if ($cartDetail)
                    $cartDetail->delete();
            }
        }

    }

}
