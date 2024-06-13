<?php

namespace App\Services\OrderService;

use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PushNotification;
use App\Models\ShopProduct;
use App\Models\User;
use App\Models\UserGiftCart;
use App\Services\CoreService;
use App\Traits\Notification;

class OrderDetailService extends CoreService
{
    use Notification;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return OrderDetail::class;
    }

    public function create($order, $collection): bool
    {

        $order->orderDetails()->delete();
        foreach ($collection as $item) {
            $order->orderDetails()->create($this->setDetailParams($item));
        }
        return true;
    }

    public function createOrderUser($order, $cart_id)
    {
        $cart = Cart::find($cart_id);

        if (empty(data_get($cart, 'userCarts'))) {
            return $order;
        }

        foreach (data_get($cart, 'userCarts', []) as $userCart) {

            $cartDetails = data_get($userCart, 'cartDetails', []);

            if (empty($cartDetails)) {
                continue;
            }

            foreach ($cartDetails as $cartDetail) {

                /** @var CartDetail $cartDetail */
                /** @var ShopProduct $shopProduct */

                $shopProduct = ShopProduct::where('quantity', '>', 1)
                    ->find($cartDetail->shop_product_id);

                if (!$shopProduct) {
                    continue;
                }

                if ($shopProduct->product->gift){

                    UserGiftCart::create([
                         'user_id' => $order->user_id,
                         'shop_product_id' => $shopProduct->id,
                         'price' => $shopProduct->price,
                     ]);

                    $token = User::whereNotNull('firebase_token')
                        ->where('id',$order->user_id)
                        ->pluck('firebase_token', 'id')
                        ->first();

                    $data = [
                        'type' => PushNotification::GIFT_PRODUCT,
                        'id' => $shopProduct->id,
                    ];

                    $this->sendNotification(
                        [data_get($token, 0)],
                        $order->user->phone." sent you a gift cart",
                        data_get($shopProduct, 'id'),
                        $data,
                        [$order->gift_user_id]
                    );

                    $order->update([
                        'status' => Order::DELIVERED
                    ]);
                }

                $minQuantity = max(min($shopProduct->quantity, $cartDetail->quantity), 0);

                $order->orderDetails()->create($this->setDetailParams($cartDetail));

                $shopProduct->update(['quantity' => $shopProduct->quantity - $minQuantity]);
            }
        }
        $cart->delete();

        return $order;
    }


    private function setDetailParams($item): array
    {
        $shopProduct = ShopProduct::find($item['shop_product_id']);

        if (isset($item['qty']))
            data_set($item, 'quantity', +$item['qty']);

        $minQuantity = max(min($shopProduct->quantity, data_get($item, 'quantity', 0)), 0);

        if (data_get($item, 'bonus')) {

            data_set($item, 'origin_price', 0);
            data_set($item, 'total_price', 0);
            data_set($item, 'tax', 0);
            data_set($item, 'discount', 0);

        } else {
            $tax = (($shopProduct->price - $shopProduct->actual_discount) / 100) * ($shopProduct->tax ?? 0) * $minQuantity;

            $discount = ($minQuantity * $shopProduct->actual_discount);

            $originPrice = (($shopProduct->price - $shopProduct->actual_discount) * $minQuantity);

            $totalPrice = $originPrice + $tax;

            data_set($item, 'origin_price', round($originPrice, 2));
            data_set($item, 'total_price', round($totalPrice, 2));
            data_set($item, 'tax', round($tax, 2));
            data_set($item, 'discount', round($discount, 2));
        }
        return [
            'origin_price' => data_get($item, 'origin_price', 0),
            'tax' => data_get($item, 'tax', 0),
            'discount' => data_get($item, 'discount', 0),
            'total_price' => data_get($item, 'total_price', 0),
            'shop_product_id' => data_get($item, 'shop_product_id', 0),
            'quantity' => $minQuantity,
            'bonus' => data_get($item, 'bonus', false)
        ];
    }


}
