<?php

namespace App\Observers;

use App\Models\BonusShop;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ShopProduct;

class OrderDetailObserver
{
    public function created(OrderDetail $orderDetail)
    {
//        if ($orderDetail->bonus) {
//            $orderDetail->update(
//                [
//                    'origin_price' => 0,
//                    'total_price' => 0,
//                    'tax' => 0,
//                    'discount' => 0,
//                ]
//            );
//        }
//        $order_id = $orderDetail->order_id;
//        $order = Order::find($order_id);
//
//        $order_detail_prices = $order->orderDetails()->selectRaw('SUM(origin_price) as origin_price,SUM(total_price) as total_price, SUM(discount) as total_discount')->first();
//        $order->orderDetails->map(function ($item) {
//            $shopProduct = ShopProduct::find($item->shop_product_id);
//            if ($shopProduct) {
//                $shopProduct->decrement('quantity', $item->quantity);
//            }
//
//        });
//
//        $tax = ($order_detail_prices->origin_price  / 100) * $order->shop->tax ?? 0;
//
//        $order->update([
//            'price' => $order_detail_prices->total_price + $order->delivery_fee + $tax,
//            'total_discount' => $order_detail_prices->total_discount,
//            'tax' => $tax
//        ]);
//
//        $bonusShop = BonusShop::active()->where('order_amount', '<=', $order->price)->where('shop_id', $order->shop_id)->orderBy('order_amount', 'desc')->first();
//        if ($bonusShop) {
//            $order->update([
//                'bonus_shop_id' => $bonusShop->id
//            ]);
//        }
    }
}
