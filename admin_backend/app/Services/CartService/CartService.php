<?php

namespace App\Services\CartService;

use App\Helpers\ResponseError;
use App\Models\BonusProduct;
use App\Models\BonusShop;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\ShopProduct;
use App\Models\User;
use App\Models\UserCart;
use App\Services\CoreService;
use App\Traits\Loggable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CartService extends CoreService
{

    use Loggable;

    /**
     * @var array|Application|Request|string
     */
    private Application|Request|string|array $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = request('lang') ?? 'en';
    }

    protected function getModelClass(): string
    {
        return Cart::class;
    }

    public function create($collection): array
    {
        /**
         * variables для автокоплита
         * @var Cart $model
         * @var UserCart $userCarts
         * @var Cart $cart
         * @var User $user
         * @var UserCart $userCarts
         */

        $user = auth('sanctum')->user();
        $collection['user_id'] = $user->id;
        $collection['owner_id'] = $user->id;
        $collection['name'] = $user->name_or_email;

        $shopProduct = ShopProduct::find(data_get($collection, 'shop_product_id', 0));
        $quantity = data_get($collection, 'quantity', 0);

        $checkQuantity = $this->checkQuantity($shopProduct->id, $quantity);

        if (!data_get($checkQuantity, 'status')) {
            return $this->errorCheckQuantity($checkQuantity);
        }

        data_set($collection, 'price', ($shopProduct->price - $shopProduct->actual_discount) * $quantity);

        $cart = $this->model()
            ->where('owner_id', $user->id)
            ->where('shop_id', data_get($collection, 'shop_id', 0))
            ->first();
        if ($cart) {
            try {
                $cartId = DB::transaction(function () use ($collection, $cart, $shopProduct, $user) {

                    /** @var UserCart $userCart */
                    $userCart = UserCart::firstWhere('uuid',data_get($collection,'user_cart_uuid'));

                    if (!$userCart) {
                        $userCart = $cart->userCarts()
                            ->firstOrCreate([
                                'cart_id' => data_get($cart, 'id'),
                                'user_id' => $user->id,
                            ], $collection);
                    }

                    /** @var CartDetail $cartDetails */
                    $cartDetail = $userCart->cartDetails()->updateOrCreate([
                        'shop_product_id' => data_get($collection, 'shop_product_id'),
                        'user_cart_id' => $userCart->id,
                        'bonus' => false
                    ], [
                        'quantity' => data_get($collection, 'quantity', 0),
                        'price' => data_get($collection, 'price', 0),
                        'shop_product_id' => data_get($collection, 'shop_product_id'),
                        'user_cart_id' => $userCart->id,
                    ]);

                    $cart = $this->calculatePrice($cart->id);

                    $this->bonus($cartDetail);
                    return data_get($cart, 'id');
                });


                return [
                    'status' => true,
                    'code' => ResponseError::NO_ERROR,
                    'data' => Cart::with([
                        'userCarts.cartDetails.shopProduct.product.translation',
                        'userCarts.cartDetails.shopProduct.product.unit.translation'
                    ])->find($cartId),
                ];
            } catch (Throwable $e) {
                $this->error($e);
                return ['status' => false, 'code' => ResponseError::ERROR_501];
            }

        }

        try {
            $cartId = DB::transaction(function () use ($collection) {

                /** @var Cart $cart */
                $cart = $this->model()
                    ->create($collection);

                if ($cart) {

                    /** @var UserCart $userCarts */
                    $userCarts = $cart->userCarts()->create($collection);

                    $userCarts->cartDetails()->create($collection);

                    $cart = $this->calculatePrice($cart->id);

                }

                return data_get($cart, 'id');
            });

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => Cart::with([
                    'userCarts.cartDetails.shopProduct.product.translation',
                    'userCarts.cartDetails.shopProduct.product.unit.translation'
                ])->find($cartId),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
    }

    public function groupCreate($collection): array
    {
        $shopProduct = ShopProduct::find(data_get($collection, 'shop_product_id', 0));

        $quantity = data_get($collection, 'quantity', 0);

        data_set($collection, 'price', $shopProduct->price * $quantity);

        $checkQuantity = $this->checkQuantity($shopProduct->id, $quantity);

        if (!data_get($checkQuantity, 'status')) {
            return $this->errorCheckQuantity($checkQuantity);
        }

        /**
         * @var Cart $model
         * @var UserCart $userCart
         */
        $model = $this->model()->find(data_get($collection, 'cart_id', 0));

        $userCart = $model->userCarts->where('uuid', data_get($collection, 'user_cart_uuid'))->first();

        if (!$userCart) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        try {
            $cartId = DB::transaction(function () use ($collection, $model, $userCart) {

                /** @var CartDetail $cartDetails */
                $cartDetail = $userCart->cartDetails()->updateOrCreate([
                    'shop_product_id' => data_get($collection, 'shop_product_id'),
                    'user_cart_id' => $userCart->id,
                ], [
                    'quantity' => data_get($collection, 'quantity', 0),
                    'price' => data_get($collection, 'price', 0),
//                    'discount' => data_get($collection, 'discount', 0),
                    'shop_product_id' => data_get($collection, 'shop_product_id'),
                    'user_cart_id' => $userCart->id,
                ]);


                $cart = $this->calculatePrice($model->id);

                $this->bonus($cartDetail);

                return data_get($cart, 'id');
            });

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => Cart::with([
                    'userCarts.cartDetails.shopProduct.product.translation',
                    'userCarts.cartDetails.shopProduct.product.unit.translation'
                ])->find($cartId),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        }
    }

    public function openCart($collection): array
    {
        /** @var Cart $cart */
        $cart = $this->model()
            ->with('userCarts')
            ->where('shop_id', data_get($collection, 'shop_id', 0))
            ->find(data_get($collection, 'cart_id', 0));
        if (empty($cart)) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
        $model = $cart->userCart()->create($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
    }

    public function openCartOwner($collection): array
    {
        /**
         * variables для автокоплита
         * @var Cart $model
         * @var User $user
         */
        $user = auth('sanctum')->user();
        $model = $this->model();
        $cart = $model
            ->where('shop_id', data_get($collection, 'shop_id', 0))
            ->where('owner_id', $user->id)
            ->first();


        if (data_get($cart, 'userCart')) {
            $cart->update(['together' => true]);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $cart];
        }
        try {
            $cartId = DB::transaction(function () use ($collection, $model, $user) {
                $cart = $model->firstOrCreate([
                    'shop_id' => data_get($collection, 'shop_id', 0),
                    'owner_id' => $user->id,
                ], $collection);

                $cart->userCarts()
                    ->firstOrCreate([
                        'cart_id' => data_get($cart, 'id'),
                        'user_id' => $user->id,
                    ], $collection);

                return data_get($cart, 'id');
            });

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => $this->model()->with([
                    'userCarts.cartDetails.shopProduct.product.translation',
                    'userCarts.cartDetails.shopProduct.product.unit.translation'
                ])->find($cartId),
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        }
    }

    public function destroy(int $id): array
    {
        /** @var Cart $cart */
        $cart = $this->model()->find($id);

        if ($cart) {

            $cart->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $cart];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];

    }

    public function cartProductDelete(int $id): array
    {
        $cartDetail = CartDetail::find($id);

        if ($cartDetail) {

            $cartDetail->delete();

            CartDetail::where([
                ['bonus', true],
                ['user_cart_id', $cartDetail->user_cart_id],
                ['shop_product_id', $cartDetail->shop_product_id]
            ])->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $cartDetail];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function userCartDelete(string $uuid, int $cartId): array
    {
        /** @var Cart $cart */
        $cart = $this->model()->find($cartId);

        if (!data_get($cart, 'userCart')) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        $cart->userCart->where('uuid', $uuid)->delete();

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    private function checkQuantity(int $shop_product_id, int $quantity): array
    {
        $shopProduct = ShopProduct::where('id', $shop_product_id)->first('quantity');

        if ($shopProduct->quantity < $quantity) {
            return [
                'status' => false,
                'code' => ResponseError::NO_ERROR,
                'quantity' => $shopProduct->quantity];
        }

        return ['status' => true];
    }


    public function insertProducts(array $collection): array
    {
        /**
         * @var Cart $cart
         * @var User $user
         */
        $user = auth('sanctum')->user();
        $collection['owner_id'] = $user->id;
        $collection['user_id'] = $user->id;

        $cart = $this->model()
            ->with([
                'userCarts.cartDetails.shopProduct.product.translation',
            ])
            ->where('owner_id', $user->id)
            ->where('shop_id', data_get($collection, 'shop_id', 0))
            ->first();
        if ($cart) {
            return $this->cartDetailsUpdate($collection, $cart);
        }
        return $this->cartDetailsCreate($collection);
    }

    private function cartDetailsUpdate(array $collection, Cart $cart): array
    {
        if (!$cart->userCart) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        try {
            $cartId = DB::transaction(function () use ($collection, $cart) {

                $cartDetails = data_get($collection, 'products', []);

                foreach ($cartDetails as $cartDetail) {

                    /** @var CartDetail $findCart */
                    $findCart = $cart
                        ->userCart
                        ->cartDetails()
                        ->where('shop_product_id', data_get($cartDetail, 'shop_product_id', 0))
                        ->first();

                    $quantity = data_get($cartDetail, 'quantity', 0);

                    $shopProduct = ShopProduct::find(data_get($cartDetail, 'shop_product_id', 0));

                    if (!$findCart) {
                        $cartDetail = $cart->userCart->cartDetails()->create([
                            'shop_product_id' => $shopProduct->id,
                            'quantity' => $quantity,
                            'price' => $shopProduct->price * $quantity,
                        ]);
                        $this->bonus($cartDetail);

                    } else {
                        $totalQuantity = $quantity + $findCart->quantity;

                        $findCart->update([
                            'quantity' => $totalQuantity,
                            'price' => $shopProduct->price * $totalQuantity,
                        ]);

                        $this->bonus($findCart);
                    }
                }

                $cart = $this->calculatePrice($cart->id);

                return data_get($cart, 'id');
            });

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => $this->model()->with(['userCarts.cartDetails.shopProduct.product.translation'])->find($cartId)
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        }
    }

    private function cartDetailsCreate(array $collection): array
    {
        try {
            $cartId = DB::transaction(function () use ($collection) {
                /** @var Cart $cart */
                $cart = $this->model()
                    ->with([
                        'userCarts',
                    ])
                    ->create($collection);
                if (!$cart) {
                    return null;
                }

                /** @var UserCart $model */
                $model = $cart->userCart()->create($collection);
                $products = data_get($collection, 'products', []);

                foreach ($products as $product) {

                    /** @var ShopProduct $shopProduct */
                    $shopProduct = ShopProduct::find(data_get($product, 'shop_product_id', 0));

                    data_set($product, 'price', $shopProduct->price * data_get($product, 'quantity', 0));

                    $cartDetail = $model->cartDetails()->create($product);

                    $this->bonus($cartDetail);
                }

                $cart = $this->calculatePrice($cart->id);

                return data_get($cart, 'id');
            });

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => $this->model()->with('userCarts.cartDetails.shopProduct.product.translation')->find($cartId)
            ];

        } catch (Throwable $e) {
            $this->error($e);

            return [
                'status' => false,
                'code' => ResponseError::ERROR_400,
                'message' => __('web.user cart and cart details is empty or not found'),
            ];
        }

    }

    private function calculatePrice($cartId): Model|Collection|Cart|array|null
    {
        $cart = Cart::find($cartId);
        if (!empty(data_get($cart, 'userCart'))) {
            $price = 0;
            $quantity = 0;
            foreach ($cart->userCarts as $userCart) {
                $price += empty($userCart->cartDetails) ? 0 : $userCart->cartDetails->sum('price');
                $quantity += empty($userCart->cartDetails) ? 0 : $userCart->cartDetails->sum('quantity');
            }

            $cart->update(['total_price' => $price]);
        }

        return $cart;
    }

    public function statusChange(string $uuid, int $cartId): array
    {
        /** @var Cart $cart */
        $cart = $this->model()
            ->with('userCarts.cartDetails.shopProduct.product.translation')
            ->find($cartId);
        if (empty(data_get($cart, 'id'))) {
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }

        $userCart = $cart->userCart->where('uuid', $uuid)->first();

        if (empty($userCart)) {
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        }

        $userCart->update(['status' => !$userCart->status]);
        return [
            'status' => true,
            'code' => ResponseError::NO_ERROR,
            'data' => $userCart
        ];
    }

    private function bonus($cartDetail)
    {
        /** @var CartDetail $cartDetail */
        $shopProduct = $cartDetail->shopProduct;

        $bonusProduct = BonusProduct::where([
            ['shop_product_id', $shopProduct->id],
            ['expired_at', '>', now()],
            ['status', true],
        ])->whereHas('shopProduct.product')->first();
        if ($bonusProduct) {
            $this->checkBonusProduct($cartDetail, $bonusProduct);
        }

//        if ($bonusShop){
//            $this->checkBonusShop($cartDetail,$bonusShop);
//        }
    }

    private function checkBonusProduct(CartDetail $cartDetail, BonusProduct $bonusProduct)
    {
        $shopProduct = CartDetail::where('shop_product_id', $bonusProduct->shop_product_id)
            ->where('quantity', '<', $bonusProduct->shop_product_quantity)
            ->where('user_cart_id', $cartDetail->user_cart_id)
            ->where('bonus', false)->first();

        if ($shopProduct) {
            CartDetail::where('user_cart_id', $cartDetail->user_cart_id)
                ->where('bonus', true)
                ->where('shop_product_id', $bonusProduct->bonus_product_id)
                ?->delete();
        } else {
            $cartDetail->userCart->cartDetails()->updateOrCreate([
                'shop_product_id' => $bonusProduct->bonus_product_id,
                'user_cart_id' => $cartDetail->user_cart_id,
                'price' => 0,
                'bonus' => true], [
                'quantity' => floor($cartDetail->quantity / $bonusProduct->shop_product_quantity) * $bonusProduct->bonus_quantity,
            ]);
        }
    }


    private function errorCheckQuantity($checkQuantity): array
    {
        return [
            'status' => false,
            'code' => ResponseError::ERROR_111,
            'data' => [
                'quantity' => data_get($checkQuantity, 'quantity', 0)
            ]
        ];
    }


}
