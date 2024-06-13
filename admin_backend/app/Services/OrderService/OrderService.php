<?php

namespace App\Services\OrderService;

use App\Helpers\NotificationHelper;
use App\Helpers\ResponseError;
use App\Helpers\Utility;
use App\Models\BonusShop;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CoreService;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\ShopProductService\ShopProductService;
use App\Services\TransactionService\TransactionService;
use App\Traits\Notification;
use Carbon\Carbon;
use DB;
use Exception;
use Throwable;

class OrderService extends CoreService implements OrderServiceInterface
{
    use Notification;

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Order::class;
    }

    public function create($collection): array
    {
        $shop = Shop::find($collection['shop_id']);
        /** @var Order $order */
        $order = $this->model()->create($this->setOrderParams($collection));

        if ($order) {

            if (isset($collection['images'])) {
                $order->update(['img' => $collection['images'][0]]);
                $order->uploads($collection['images']);
            }
            if ($order->deliveryType?->type == Delivery::TYPE_PICKUP) {
                $order->update(['delivery_fee' => 0]);
//                $this->calculateDeliveryFee($collection, $shop, $order);
            }

            if (data_get($collection, 'cart_id')) {
                (new OrderDetailService())->createOrderUser($order, data_get($collection, 'cart_id'));
            } else {
                (new OrderDetailService())->create($order, data_get($collection, 'products', []));
                (new ShopProductService())->decrementStocksQuantity(data_get($collection, 'products'));
            }

            $orderDetails = $order->orderDetails();

            $tax = ($orderDetails->sum('origin_price') / 100) * data_get($order, 'shop.tax', 1) + $orderDetails->sum('tax');

            $totalPrice = $orderDetails->sum('origin_price') + $tax;

            $order->update([
                'price' => $totalPrice
            ]);

            if (isset($collection['coupon'])) {
                $this->checkCoupon($collection['coupon'], $order);
            }

            $totalPrice = $order->price + $order->delivery_fee;

            $totalPrice = max($totalPrice, 0);

            $order->update([
                'price' => $totalPrice,
                'commission_fee' => ($totalPrice / 100 * $shop?->percentage <= 0.99 ? 1 : $shop?->percentage),
                'total_discount' => $orderDetails->sum('discount'),
                'tax' => $tax
            ]);

            if ($order->gift_cart_id){
                $this->setGiftOrder($order);
            }

            if (isset($collection['auto_order']) && $collection['auto_order'] !== false) {

                $orderTemplate = $order->orderTemplate()->create($collection);

                $isAutoOrder = Settings::where('key', 'auto_order')->where('value', 1)->first();

                if ($isAutoOrder) {
                    $this->createAutoOrder($orderTemplate,$collection);
                }
            }

            return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $order];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];

    }

    public function update(int $id, $collection): array
    {
        $shop = Shop::find($collection['shop_id']);
        try {
            $order = $this->model()->find($id);
            if ($order) {

                if (isset($collection['images'])) {
                    $order->update(['img' => $collection['images'][0]]);
                    $order->uploads($collection['images']);
                }

                if ($order?->deliveryType->type !== Delivery::TYPE_PICKUP) {
                    $this->calculateDeliveryFee($collection, $shop, $order);
                }
                $collection['commission_fee'] = ($collection['total'] / 100 * $shop->percentage);
                $order->update($this->setOrderParams($collection));
                (new ShopProductService())->incrementStocksQuantity($collection['products']);
                (new OrderDetailService())->create($order, $collection['products']);
                (new ShopProductService())->decrementStocksQuantity($collection['products']);

                return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $order];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    private function setOrderParams($collection): array
    {
        return [
            'user_id' => $collection['user_id'] ?? auth('sanctum')->id(),
            'price' => $collection['total'] ?? 0,
            'currency_id' => $collection['currency_id'] ?? Currency::whereDefault(1)->pluck('id')->first(),
            'rate' => $collection['rate'],
            'note' => $collection['note'] ?? null,
            'shop_id' => $collection['shop_id'],
            'status' => $collection['status'] ?? 'new',
            'delivery_type_id' => $collection['delivery_type_id'] ?? null,
            'delivery_fee' => $collection['delivery_fee'] ?? 0,
            'delivery_address_id' => $collection['delivery_address_id'] ?? null,
            'deliveryman' => $collection['deliveryman'] ?? null,
            'delivery_date' => $collection['delivery_date'] ?? null,
            'delivery_time' => $collection['delivery_time'] ?? null,
            'total_discount' => $collection['total_discount'] ?? null,
            'branch_id' => $collection['branch_id'] ?? null,
            'name' => $collection['name'] ?? null,
            'phone' => $collection['phone'] ?? null,
            'auto_order' => $collection['auto_order'] ?? null,
            'money_back' => $collection['money_back'] ?? null,
            'img' => $collection['img'] ?? null,
            'gift_user_id' => $collection['gift_user_id'] ?? null,
            'gift_cart_id' => $collection['gift_cart_id'] ?? null
        ];
    }

    private function calculateDeliveryFee(array $collection, Shop $shop, Order $order)
    {
        $helper = new Utility;

        $km = $helper->getDistance($shop?->location, $order?->deliveryAddress?->location);

        $deliveryFee = $helper->getPriceByDistance($km, $shop, (float)data_get($collection, 'rate', 1));

        $deliveryFeeRate = $deliveryFee;

        $order->update([
            'delivery_fee' => $deliveryFeeRate
        ]);
    }

    public function createAutoOrder($orderTemplate, $collection)
    {
        $this->setOrderTemplate($orderTemplate, $collection);
    }

    public function updateAutoOrder($orderTemplate, $collection)
    {
        $endDate = $collection['date']['end_date'];

        $endDateOrderTemplate = $orderTemplate->date['end_date'];

        if ($endDate < $endDateOrderTemplate){

            $endDate = Carbon::createFromFormat('Y-m-d',$endDate);

            $endDateOrderTemplate = Carbon::createFromFormat('Y-m-d',$endDateOrderTemplate);

            $diffInDays = $endDate->diffInDays($endDateOrderTemplate);

            for ($i = 0; $i++, $i <= $diffInDays;) {

                $orderDate = $endDate->addDays(1);

                $order = Order::where('order_template_id',$orderTemplate?->id)
                    ->whereDate('created_at',$orderDate->format('Y-m-d'))
                    ->first();

                $orderDetails = OrderDetail::where('order_id',$order?->id)->get();

                foreach ($orderDetails as $orderDetail){
                    $orderDetail->delete();
                }

                Transaction::where('payable_id',$order?->id)
                    ->where('payable_type',Order::class)
                    ?->delete();

                $order->delete();
            }
        }

        $this->setOrderTemplate($orderTemplate,$collection);
    }

    private function setOrderTemplate($orderTemplate, $collection)
    {
        $startDate = Carbon::createFromFormat('Y-m-d',$collection['date']['start_date']);

        $endDate = Carbon::createFromFormat('Y-m-d',$collection['date']['end_date']);

        $orderTemplate->update($collection);

        $diffInDays = $startDate->diffInDays($endDate);

        for ($i = 0; $i++, $i <= $diffInDays;) {

            $startDate = $startDate->addDays(1);

            $order = Order::create($orderTemplate?->order()?->first()->toArray());

            $order->update([
                'created_at' => $startDate,
                'order_template_id' => $orderTemplate->id
            ]);

            if(!isset($collection['payment_sys_id'])){
                $collection['payment_sys_id'] = $orderTemplate?->order()?->first()?->transaction?->payment_sys_id;
            }

            if(isset($collection['payment_sys_id'])){
                (new TransactionService())->orderTransaction($order->id, $collection);
            }

            $orderDetailIds = $orderTemplate?->order()?->first()?->orderDetails()?->pluck('id');

            $orderDetails = OrderDetail::find($orderDetailIds);

            foreach ($orderDetails as $orderDetail) {

                $orderDetail->replicate()->save();

                $orderDetail->update([
                    'order_id' => $order?->id
                ]);
            }
        }
    }

    private function checkCoupon($couponName, $order)
    {
        $coupon = Coupon::checkCoupon($couponName)->first();

        if ($coupon) {

            $couponPrice = $coupon->price;

            if ($coupon->type == Coupon::TYPE_PERCENT) {
                $couponPrice = ($order->price / 100) * $coupon->price;
            }

            $order->update(['price' => $order->price - $couponPrice]);
            $order->coupon()->create([
                'user_id' => $order->user_id,
                'name' => $coupon->name,
                'price' => $couponPrice,
            ]);

            $coupon->decrement('qty');
        }
    }

    public function updateStatus($order, $status): array
    {
        // Order Status change logic
        return (new OrderStatusUpdateService())->statusUpdate($order, $status);

    }

    public function orderProductsCalculate(int $id): array
    {
        $currencyRate = Currency::where('id', request('currency_id'))
            ->orWhere('default', 1)
            ->first()
            ->rate;
        $cart = Cart::find($id);

        $ids = $cart->userCarts->map(function ($q) {
            return $q->cartDetails->pluck('id')->toArray();
        });

        $ids = array_reduce($ids->toArray(), 'array_merge', array());

        $details = CartDetail::whereIn('id', $ids)->get();

        $details = $details->map(function ($cartDetail) use ($currencyRate) {
            /**
             * variables для автокоплита
             * @var ShopProduct $shopProduct
             * @var CartDetail $cartDetail
             */

            $shopProduct = $cartDetail->shopProduct->with('product.unit.translation', 'product.translation')
                ->where('quantity', '>', 0)
                ->whereHas('product')
                ->find($cartDetail->shop_product_id);

            if (!$shopProduct) {
                return null;
            }

            $perPrice = ($shopProduct->price / $currencyRate - $shopProduct->actual_discount);

            $initialPrice = $perPrice * $cartDetail->quantity;
            // Get Product Price Tax minus discount
            $tax = ($initialPrice / 100) * ($shopProduct->tax / $currencyRate ?? 0);
            // Get Product Shop Tax amount
            $shopTax = ($initialPrice / 100 * ($shopProduct?->shop?->tax / $currencyRate ?? 0));
            // Get Total Product Price with Tax, Discount and Quantity
            $totalPrice = $initialPrice + $tax;

            if ($cartDetail->bonus) {
                $bonusProduct = $cartDetail
                    ?->shopProduct
                    ?->productBonus
                    ?->shopProduct
                    ->where('quantity', '>', 0)
                    ->first();

                if (!$bonusProduct) {
                    return null;
                }
                $initialPrice = 0;
                $tax = 0;
                $shopTax = 0;
                $totalPrice = 0;
            }

            return [
                'id' => (int)$shopProduct->id,
                'price' => round($perPrice, 2),
                'qty' => $cartDetail->quantity,
                'tax' => round($tax, 2),
                'shop_tax' => round($shopTax, 2),
                'discount' => $cartDetail->bonus ? 0 : round(($shopProduct->actual_discount * $cartDetail->quantity), 2),
                'price_without_tax' => round($initialPrice, 2),
                'total_price' => round($totalPrice, 2),
                'bonus' => (bool)$cartDetail->bonus,
                'product' => $shopProduct->product,
            ];
        })
            ->filter()
            ->values();

        $orderTotal = round($details->sum('price_without_tax') + $details->sum('tax') + $details->sum('shop_tax'), 2);
        $bonusShop = BonusShop::with([
            'shopProduct.product.translation' => fn($q) => $q
                ->select('id', 'product_id', 'locale', 'title')])
            ->whereHas('shopProduct.product')
            ->where('order_amount', '<=', $orderTotal)
            ->where('shop_id', $cart->shop_id)
            ->where('status', true)
            ->orderBy('order_amount', 'desc')
            ->get();

        return [
            'products' => $details,
            'product_tax' => $details->sum('tax'),
            'product_total' => round($details->sum('price_without_tax'), 2),
            'order_tax' => round($details->sum('shop_tax'), 2),
            'order_total' => round($details->sum('price_without_tax') + $details->sum('tax') + $details->sum('shop_tax'), 2),
            'total_discount' => $details->sum('discount'),
            'bonus_shop' => $bonusShop
        ];
    }

    public function productsCalculate($array): array
    {
        // Get Product ID from Request
        $id = collect($array['products'])->pluck('id');
        /**
         * variables для автокоплита
         * @var ShopProduct $products
         */
        $products = ShopProduct::with('product.translation')->find($id);


        $products = $products->map(function ($item) use ($array) {
            $quantity = $item->quantity;  // Set Stock Quantity
            $price = $item->price;  // Set Stock price
            foreach ($array['products'] as $product) {
                if ($item->id == $product['id']) {
                    // Set new Product quantity if it less in the stock
                    $quantity = min($item->quantity, $product['quantity']);
                }
            }
            // Get actual discount
            $discount = $item->actual_discount;
            // Get Product Price Tax minus discount
            $tax = (($price - $discount) / 100) * ($item->tax ?? 0);
            // Get Product Price without Tax for Order Total
            $priceWithoutTax = ($price - $discount) * $quantity;
            // Get Product Shop Tax amount
            $shopTax = ($priceWithoutTax / 100 * ($item->shop->tax ?? 0));

            // Get Total Product Price with Tax, Discount and Quantity
            $totalPrice = (($price - $discount) + $tax) * $quantity;

            return [
                'id' => (int)$item->id,
                'price' => round($price, 2),
                'qty' => (int)$quantity,
                'tax' => round(($tax * $quantity), 2),
                'shop_tax' => round($shopTax, 2),
                'discount' => round(($discount * $quantity), 2),
                'price_without_tax' => round($priceWithoutTax, 2),
                'total_price' => round($totalPrice, 2),
                'translation' => $item?->product?->translation

            ];
        });

        return [
            'products' => $products,
            'product_tax' => $products->sum('tax'),
            'product_total' => round($products->sum('price_without_tax'), 2),
            'order_tax' => round($products->sum('shop_tax'), 2),
            'order_total' => round($products->sum('price_without_tax') + $products->sum('tax') + $products->sum('shop_tax'), 2)
        ];
    }

    /**
     * @param int $id
     * @param int|null $userId
     * @return array
     */
    public function setCurrent(int $id, ?int $userId = null): array
    {
        $errors = [];

        $orders = Order::when($userId, fn($q) => $q->where('deliveryman', $userId))
            ->where('current', 1)
            ->orWhere('id', $id)
            ->get();

        $getOrder = new Order;

        foreach ($orders as $order) {

            try {

                if ($order->id === $id) {

                    $order->update([
                        'current' => true,
                    ]);

                    $getOrder = $order;

                    continue;

                }

                $order->update([
                    'current' => false,
                ]);

            } catch (Throwable $e) {
                $errors[] = $order->id;

                $this->error($e);
            }

        }

        return count($errors) === 0 ? [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
            'data' => $getOrder
        ] : [
            'status' => false,
            'code' => ResponseError::ERROR_400,
            'message' => 'Can`t update orders #' . implode(', #', $errors)
        ];
    }

    /**
     * @param int|null $id
     * @return array
     */
    public function attachDeliveryMan(?int $id): array
    {
        try {

            /** @var Order $order */
            /** @var User $user */
            $order = Order::with('user')->find($id);

            if (empty($order) || !in_array($order->deliveryType?->type, [Delivery::TYPE_STANDARD, Delivery::TYPE_EXPRESS])) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_404,
                    'message' => 'Invalid deliveryman or token not found'
                ];
            }

            if (!empty($order->deliveryman)) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_210,
                    'message' => 'Delivery already attached'
                ];
            }
            $user = auth('sanctum')->user();
            if (!$user?->invitations?->where('shop_id', $order->shop_id)?->first()?->id) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_212,
                    'message' => 'Not your shop. Check your other account'
                ];
            }

            $order->update([
                'deliveryman' => auth('sanctum')->id(),
            ]);

            if ($order->transaction->status == Transaction::DEBIT) {

                /** @var User $user */

                $user = auth('sanctum')->user();

                (new TransactionService())->payDebit($user, $order);
            }

            return ['status' => true, 'message' => ResponseError::NO_ERROR, 'data' => $order];
        } catch (Throwable) {
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => ResponseError::ERROR_501];
        }
    }

    /**
     * @param int|null $orderId
     * @param int $deliveryman
     * @param int|null $shopId
     * @return array
     */
    public function updateDeliveryMan(?int $orderId, int $deliveryman, int $shopId = null): array
    {
        try {
            /** @var Order $order */
            $order = Order::when($shopId, fn($q) => $q->where('shop_id', $shopId))->find($orderId);

            if (!$order) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_404,
                    'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
                ];
            }

            /** @var User $user */
            $user = User::with('deliveryManSetting')->find($deliveryman);

            if (!$user || !$user->hasRole('deliveryman')) {
                return [
                    'status' => false,
                    'code' => ResponseError::ERROR_211,
                    'message' => __('errors.' . ResponseError::ERROR_211, locale: $this->language)
                ];
            }

//            if (!$user->invitations?->where('shop_id', $order->shop_id)?->first()?->id) {
//                return [
//                    'status'  => false,
//                    'code'    => ResponseError::ERROR_212,
//                    'message' => __('errors.' . ResponseError::ERROR_212, locale: $this->language)
//                ];
//            }

            $order->update([
                'deliveryman' => $user->id,
            ]);

            if ($order?->transaction?->status == Transaction::DEBIT) {
                (new TransactionService())->payDebit($user, $order);
            }

            $this->sendNotification(
                is_array($user->firebase_token) ? $user->firebase_token : [$user->firebase_token],
                __('errors.' . ResponseError::NEW_ORDER, ['id' => $order->id], $this->language),
                $order->id,
                (new NotificationHelper)->deliveryManOrder($order, $this->language, 'new_order'),
                [$user->id]
            );

            return [
                'status' => true,
                'message' => ResponseError::NO_ERROR,
                'data' => $order,
                'user' => $user
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status' => false,
                'code' => ResponseError::ERROR_501,
                'message' => __('errors.' . ResponseError::ERROR_501, locale: $this->language)
            ];
        }
    }

    public function debitOrderTransactionStatusChange(int $id, array $collection): array
    {
        $order = Order::when(isset($collection['deliveryman_id']), function ($q) use ($collection) {
            $q->where('deliveryman', $collection['deliveryman_id']);
        })->find($id);

        if ($order) {
            try {
                DB::beginTransaction();
                $order->transaction->update([
                    'request' => $collection['status']
                ]);

                if ($order->transaction->request == Transaction::REQUEST_APPROVED) {
                    $order->transaction->update([
                        'status' => Transaction::PAID
                    ]);
                }
                DB::commit();
            } catch (Throwable $t) {
                DB::rollBack();
            }

            return [
                'status' => true,
                'message' => ResponseError::NO_ERROR,
                'data' => $order,
            ];
        }
        return [
            'status' => false,
            'code' => ResponseError::ERROR_404,
        ];
    }

    public function debitOrderTransactionStatusChangeSeller(int $id, array $collection): array
    {

        $order = Order::when(isset($collection['shop_id']), function ($q) use ($collection) {
            $q->where('shop_id', $collection['shop_id']);
        })->find($id);

        if ($order) {
            try {
                DB::beginTransaction();
                $order->transaction?->transaction?->update([
                    'request' => $collection['status']
                ]);

                $order->transaction->update([
                    'request' => $collection['status']
                ]);

                if ($order->transaction?->transaction?->request == Transaction::REQUEST_APPROVED) {
                    $order->transaction?->transaction?->update([
                        'status' => Transaction::PAID
                    ]);
                    $order->transaction->update([
                        'status' => Transaction::PAID
                    ]);
                }

                DB::commit();
            } catch (Throwable $t) {
                DB::rollBack();
            }


            return [
                'status' => true,
                'message' => ResponseError::NO_ERROR,
                'data' => $order,
            ];
        }
        return [
            'status' => false,
            'code' => ResponseError::ERROR_404,
        ];

    }

    public function setGiftOrder($order)
    {
        if ($order->price < $order?->giftCart?->price)
        {
            $price = $order->giftCart->price - $order->price;

            $order->update([
                'price' => 0,
            ]);

            $order->giftCart->update([
                'price' => $price
            ]);
        }else{
            $price = $order->price - $order->giftCart?->price;

            $order->update([
                'price' => $price
            ]);

            $order->giftCart->update([
                'price' => 0
            ]);

        }
    }

}
