<?php

namespace App\Repositories\OrderRepository;

use App\Models\OrderDetail;
use App\Models\ShopProduct;
use App\Repositories\CoreRepository;

class OrderDetailRepository extends CoreRepository
{

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

    public function paginate($perPage = 15, $userId = null, $array = []){
        return $this->model()->withCount(['products'])
            ->with([
                'shopProduct.product',
                'order.currency:id,title,symbol',
                'shopProduct.product.translation:id,product_id,locale,title'
            ])
            ->filter($array)
            ->when(isset($userId), function ($q) use($userId) {
                $q->where('user_id', $userId);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function orderDetailById(int $id){
        return $this->model()->with([
            'deliveryman',
            'deliveryAddress',
            'deliveryType'
        ])->find($id);
    }

    public function orderProductsCalculate($array): array
    {
        // Get Product ID from Request
        $id = collect($array['products'])->pluck('id');

        // Find Products in DB
        $products = ShopProduct::with('product.shop')->find($id);
        $products = $products->map(function ($item) use ($array) {
            $quantity = $item->quantity;  // Set Stock Quantity
            $price = $item->price;  // Set Stock price
            foreach ($array['products'] as $product) {
                if ($item->id == $product['id']) {
                    // Set new Product quantity if it less in the stock
                    $quantity = min($item->quantity, $product['quantity']);
                }
            }

            // Get Product Price Tax minus discount
            $tax = (($price - $item->actual_discount) / 100) * ($item->product->tax ?? 0);
            // Get Product Price without Tax for Order Total
            $priceWithoutTax = ($price - $item->actual_discount) * $quantity;
            // Get Product Shop Tax amount
            $shopTax = ($priceWithoutTax / 100 * ($item->shop->tax ?? 0));
            // Get Total Product Price with Tax, Discount and Quantity
            $totalPrice = (($price - $item->actual_discount) + $tax) * $quantity;

            return [
                'id' => (int) $item->id,
                'price' => round($price, 2),
                'qty' => (int) $quantity,
                'tax' => round(($tax * $quantity), 2),
                'shop_tax' => round($shopTax, 2),
                'discount' => round(($item->actual_discount * $quantity), 2),
                'price_without_tax' => round($priceWithoutTax, 2),
                'total_price' => round($totalPrice, 2),
            ];
        });

       return [
           'products' =>  $products,
           'product_tax' =>  $products->sum('tax'),
           'product_total' =>  round($products->sum('price_without_tax'), 2),
           'order_tax' =>  round($products->sum('shop_tax'), 2),
           'order_total' =>  round($products->sum('price_without_tax') + $products->sum('tax') + $products->sum('shop_tax'), 2)
       ];
    }
}


//  'id' => (int) $item->id,
//  'price' => round($price * $currency->rate, 2),
//  'qty' => (int) $quantity,
//  'tax' => round(($tax * $quantity) * $currency->rate, 2),
//  'shop_tax' => round($shopTax * $currency->rate, 2),
//  'discount' => round(($item->actualDiscount * $quantity) * $currency->rate, 2),
//  'price_without_tax' => round($priceWithoutTax * $currency->rate, 2),
//  'total_price' => round($totalPrice * $currency->rate, 2),
