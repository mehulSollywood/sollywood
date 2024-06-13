<?php

namespace App\Observers;

use App\Jobs\AttachDeliveryMan;
use App\Models\BonusShop;
use App\Models\Language;
use App\Models\Order;
use App\Models\Settings;
use App\Traits\Notification;

class OrderObserver
{
    use Notification;

//    public function created(Order $order)
//    {
//        if ($order->status === Order::ACCEPTED && empty($order->deliveryman) && $this->autoDeliveryMan()) {
//            AttachDeliveryMan::dispatchAfterResponse($order, $this->language());
//        }
//        $this->addShopBonus($order);
//    }

    public function updated(Order $order)
    {
        if ($order->status === Order::ACCEPTED && empty($order->deliveryman) && $this->autoDeliveryMan()) {
            AttachDeliveryMan::dispatchAfterResponse($order, $this->language());
        }
        $this->add($order);
    }

    /**
     * @return string
     */
    public function language(): string
    {
        return request(
            'lang',
            data_get(Language::where('default', 1)->first(['locale', 'default']), 'locale')
        );
    }

    /**
     * @return bool
     */
    public function autoDeliveryMan(): bool
    {
        $autoDeliveryMan = Settings::adminSettings()->where('key', 'order_auto_delivery_man')->first();

        return (int)data_get($autoDeliveryMan, 'value', 0) === 1;
    }

    protected function add($order): void
    {
        /** @var Order $order */

        $query = BonusShop::query()->where('shop_id',$order->shop_id)
            ->whereHas('shopProduct',function($q){
                $q->where('quantity', '>' ,0);
            })
            ->whereHas('shopProduct.product')
            ->whereDate('expired_at', '>=', now())
            ->where('status',true);

        if ($query->first()){

            $shopProductBonus = $query->where('order_amount','<',$order->price)
                ->first();

            if ($shopProductBonus)
            {
                $orderDetail = $order->orderDetails
                    ->where('shop_product_id',$shopProductBonus->bonus_product_id)
                    ->where('bonus',true)
                    ->first();

                if (!$orderDetail){
                    $order->orderDetails()->create([
                        'quantity'        => $shopProductBonus->bonus_quantity,
                        'shop_product_id' => $shopProductBonus->bonus_product_id,
                        'bonus'           => true,
                        'origin_price'    => 0,
                        'total_price'     => 0,
                        'tax'             => 0,
                        'discount'        => 0,
                    ]);
                }
            }
        }

//        $query = $query->where('order_amount','>',$order->price);

//        if ($query->first()){
//            $orderDetail = $order->orderDetails
//                ->where('shop_product_id',$query->bonus_product_id)
//                ->where('bonus',true)
//                ->first();
//            if ($orderDetail)
//            {
//                $orderDetail->delete();
//            }
//        }
    }
}
