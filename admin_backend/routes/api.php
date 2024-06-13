<?php

use App\Http\Controllers\API\v1\PushNotificationController;
use App\Http\Controllers\API\v1\Rest;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\Dashboard\User;
use App\Http\Controllers\API\v1\Dashboard\Admin;
use App\Http\Controllers\API\v1\Dashboard\Payment;
use App\Http\Controllers\API\v1\Dashboard\Seller;
use App\Http\Controllers\API\v1\GalleryController;
use App\Http\Controllers\API\v1\Auth\LoginController;
use App\Http\Controllers\API\v1\Dashboard\Deliveryman;
use App\Http\Controllers\API\v1\Auth\RegisterController;
use App\Http\Controllers\API\v1\Auth\UserReferralController;
use App\Http\Controllers\API\v1\Auth\VerifyAuthController;
use App\Http\Controllers\API\v1\Dashboard\Payment\TransactionController;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'middleware' => ['localization']], function () {
    // Methods without AuthCheck
    Route::post('/auth/register', [RegisterController::class, 'register'])->middleware('sessions');
    Route::post('/auth/register/referral', [UserReferralController::class, 'userReferral'])->middleware('sessions');
    Route::post('/auth/register/login', [LoginController::class, 'loginRegister'])->middleware('sessions');
    Route::post('/auth/login', [LoginController::class, 'login'])->middleware('sessions');
    Route::post('/auth/logout', [LoginController::class, 'logout'])->middleware('sessions');
    Route::post('/auth/verify/phone', [VerifyAuthController::class, 'verifyPhone'])->middleware('sessions');
    Route::post('/auth/resend-verify', [VerifyAuthController::class, 'resendVerify'])->middleware('sessions');
    Route::get('/auth/verify/{hash}', [VerifyAuthController::class, 'verifyEmail'])
        ->middleware('sessions');
    Route::post('/auth/check/phone', [LoginController::class, 'checkPhone'])->middleware('sessions');

    Route::post('/auth/after-verify', [VerifyAuthController::class, 'afterVerifyEmail'])->middleware('sessions');

    Route::post('/auth/forgot/password', [LoginController::class, 'forgetPassword'])->middleware('sessions');

    Route::post('/auth/forgot/password/confirm', [LoginController::class, 'forgetPasswordVerify'])->middleware('sessions');

    Route::post('/auth/forgot/email-password', [LoginController::class, 'forgetPasswordEmail'])->middleware('sessions');

    Route::post('/auth/forgot/email-password/{hash}', [LoginController::class, 'forgetPasswordVerifyEmail'])
        ->middleware('sessions');

    // Route::get('/login/{provider}', [LoginController::class,'redirectToProvider']);
    Route::post('/auth/{provider}/callback', [LoginController::class, 'handleProviderCallback']);


    Route::group(['prefix' => 'install'], function () {
        Route::get('/init/check', [Rest\InstallController::class, 'checkInitFile']);
        Route::post('/init/set', [Rest\InstallController::class, 'setInitFile']);
        Route::post('/database/update', [Rest\InstallController::class, 'setDatabase']);
        Route::post('/admin/create', [Rest\InstallController::class, 'createAdmin']);
        Route::post('/migration/run', [Rest\InstallController::class, 'migrationRun']);
        Route::post('/check/licence', [Rest\InstallController::class, 'licenceCredentials']);
        Route::post('/currency/create', [Rest\InstallController::class, 'createCurrency']);
        Route::post('/languages/create', [Rest\InstallController::class, 'createLanguage']);
    });

    Route::group(['prefix' => 'rest'], function () {


        /* Languages */
        Route::get('translations/paginate', [Rest\SettingController::class, 'translationsPaginate']);
        Route::get('settings', [Rest\SettingController::class, 'settingsInfo']);
        Route::get('referral', [Rest\SettingController::class, 'referral']);
        Route::get('system/information', [Rest\SettingController::class, 'systemInformation']);

        /* Languages */
        Route::get('languages/default', [Rest\LanguageController::class, 'default']);
        Route::get('languages/active', [Rest\LanguageController::class, 'active']);
        Route::get('languages/{id}', [Rest\LanguageController::class, 'show']);
        Route::get('languages', [Rest\LanguageController::class, 'index']);

        /* Currencies */
        Route::get('currencies', [Rest\CurrencyController::class, 'index']);

        /* CouponCheck */
        Route::post('coupons/check', Rest\CouponController::class);
      

        /* Recipe Category*/
        Route::get('recipe-category/paginate', [Rest\RecipeCategoryController::class, 'index']);
        Route::get('recipe-category/{id}', [Rest\RecipeCategoryController::class, 'show']);
        Route::get('products/buy-with/{id}', [Rest\ProductController::class, 'buyWithProduct']);

        /* Recipe */
        Route::get('recipe/paginate', [Rest\RecipeController::class, 'index']);
        Route::get('recipe/{id}', [Rest\RecipeController::class, 'show']);

        /* Products */
        Route::post('products/review/{uuid}', [Rest\ProductController::class, 'addProductReview']);
        Route::get('products/calculate', [Rest\ProductController::class, 'productsCalculate']);
        Route::get('products/paginate', [Rest\ProductController::class, 'paginate']);
        Route::get('products/{uuid}', [Rest\ProductController::class, 'show']);
        Route::get('products/slug/{slug}', [Rest\ProductController::class, 'showBySlug']);
        Route::get('products/brand/{id}', [Rest\ProductController::class, 'productsByBrand']);
        Route::get('products/shop/{uuid}', [Rest\ProductController::class, 'productsByShopUuid']);
        Route::get('products/category/{uuid}', [Rest\ProductController::class, 'productsByCategoryUuid']);
        Route::get('products/search', [Rest\ProductController::class, 'productsSearch']);
        Route::get('products/most-sold', [Rest\ProductController::class, 'mostSoldProducts']);
        Route::get('products/discount', [Rest\ProductController::class, 'discountProducts']);
        Route::get('products/ids', [Rest\ProductController::class, 'productsByIDs']);

        /* Categories */
        Route::get('categories/paginate', [Rest\CategoryController::class, 'paginate']);
        Route::get('categories/slug/{slug}', [Rest\CategoryController::class, 'showBySlug']);
        Route::get('categories/parent', [Rest\CategoryController::class, 'parentCategory']);
        Route::get('categories/children/{id}', [Rest\CategoryController::class, 'childrenCategory']);
        Route::get('categories/select-paginate', [Rest\CategoryController::class, 'selectPaginate']);
        Route::get('categories/product/paginate', [Rest\CategoryController::class, 'shopCategoryProduct']);
        Route::get('categories/search', [Rest\CategoryController::class, 'categoriesSearch']);
        Route::get('categories/{uuid}', [Rest\CategoryController::class, 'show']);

        /* Brands */
        Route::get('brands/paginate', [Rest\BrandController::class, 'paginate']);
        Route::get('brands/slug/{slug}', [Rest\BrandController::class, 'showBySlug']);
        Route::get('brands/{id}', [Rest\BrandController::class, 'show']);


        /* Shops */
        Route::get('shops/paginate', [Rest\ShopController::class, 'paginate']);
        Route::get('shops/nearby', [Rest\ShopController::class, 'nearbyShops']);
        Route::get('shops/search', [Rest\ShopController::class, 'shopsSearch']);
        Route::get('shops/deliveries', [Rest\ShopController::class, 'shopsDeliveryByIDs']);
        Route::get('shops/{uuid}', [Rest\ShopController::class, 'show']);
        Route::get('shops', [Rest\ShopController::class, 'shopsByIDs']);
        Route::get('shops/byId/{id}', [Rest\ShopController::class, 'showById']);
        Route::get('shops/by-slug/{slug}', [Rest\ShopController::class, 'showBySlug']);


        /* Banners */
        Route::get('banners/paginate', [Rest\BannerController::class, 'paginate']);
        Route::get('banners/{id}/products', [Rest\BannerController::class, 'bannerProducts']);
        Route::get('banners/{id}', [Rest\BannerController::class, 'show']);

        /* FAQS */
        Route::get('faqs/paginate', [Rest\FAQController::class, 'paginate']);

        /* Payments */
        Route::get('payments', [Rest\PaymentController::class, 'index']);
        Route::get('admin/payments', [Rest\PaymentController::class, 'adminPayment']);
        Route::get('payments/{id}', [Rest\PaymentController::class, 'show']);

        /* Blogs */
        Route::get('blogs/paginate', [Rest\BlogController::class, 'paginate']);
        Route::get('blogs/{uuid}', [Rest\BlogController::class, 'show']);

        /* Cashback check */
        Route::post('cashback/check', [Rest\ProductController::class, 'checkCashback']);
        Route::post('cashback', [Rest\ProductController::class, 'getCashback']);
        Route::get('cashback/notification', [Rest\ProductController::class, 'CashbackNotification']);
   
        
        Route::delete('cart/product/{cart_detail_id}', [Rest\CartController::class, 'cartProductDelete']);
        Route::post('cart', [Rest\CartController::class, 'store']);
        Route::post('cart/open', [Rest\CartController::class, 'openCart']);
        Route::get('cart/{id}', [Rest\CartController::class, 'get']);
        Route::delete('cart/member/{user_cart_uuid}', [Rest\CartController::class, 'userCartDelete']);
        Route::post('cart/status/{user_cart_uuid}', [Rest\CartController::class, 'statusChange']);
        Route::get('groups', [Rest\GroupController::class, 'paginate']);

        Route::get('term', [Rest\FAQController::class, 'term']);

        Route::get('policy', [Rest\FAQController::class, 'policy']);

        Route::post('subscription', [Rest\SubscriptionController::class, 'subscription']);

        Route::get('shop/delivery-zone/{shopId}', [Rest\DeliveryZoneController::class, 'getByShopId']);
        Route::get('shop/delivery-zone/calculate/price/{id}', [
            Rest\DeliveryZoneController::class,
            'deliveryCalculatePrice'
        ]);
        Route::get('shop/delivery-zone/calculate/distance', [Rest\DeliveryZoneController::class, 'distance']);
        Route::get('shop/delivery-zone/check/distance', [Rest\DeliveryZoneController::class, 'checkDistance']);
        Route::get('shop/{id}/delivery-zone/check/distance', [Rest\DeliveryZoneController::class, 'checkDistanceByShop']);

        /* Order Statuses */
        Route::get('order-statuses', [Rest\OrderStatusController::class, 'index']);
        Route::get('order-statuses/select', [Rest\OrderStatusController::class, 'select']);

        //Parcel Orders Setting
        Route::get('parcel-order/types',[Rest\ParcelOrderSettingController::class, 'index']);
        Route::get('parcel-order/type/{id}',[Rest\ParcelOrderSettingController::class, 'show']);
        Route::get('parcel-order/calculate-price',  [Rest\ParcelOrderSettingController::class, 'calculatePrice']);

    });

    Route::group(['prefix' => 'payments', 'middleware' => ['sanctum.check'], 'as' => 'payment.'], function () {
        /* Transactions */
        Route::post('{type}/{id}/transactions', [TransactionController::class, 'store']);
        Route::put('{type}/{id}/transactions', [TransactionController::class, 'updateStatus']);
    });

    Route::group(['prefix' => 'dashboard'], function () {
        /* Galleries */
        Route::get('galleries/paginate', [GalleryController::class, 'paginate']);
        Route::get('galleries/storage/files', [GalleryController::class, 'getStorageFiles']);
        Route::post('galleries/storage/files/delete', [GalleryController::class, 'deleteStorageFile']);
        Route::apiResource('galleries', GalleryController::class);

        Route::post('user/phone/password/update', [User\ProfileController::class, 'passwordUpdateWithPhone']);
        // Notifications
        Route::apiResource('notifications',PushNotificationController::class)
            ->only(['index', 'show']);
        Route::post('notifications/{id}/read-at',   [PushNotificationController::class, 'readAt']);
        Route::post('notifications/read-all',       [PushNotificationController::class, 'readAll']);

        Route::group(['middleware' => ['sanctum.check']], function () {
            // USER BLOCK
            Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
                
                Route::get('profile/show', [User\ProfileController::class, 'show']);
                Route::put('profile/update', [User\ProfileController::class, 'update']);
                Route::delete('profile/delete', [User\ProfileController::class, 'delete']);
                Route::post('profile/firebase/token/update', [User\ProfileController::class, 'fireBaseTokenUpdate']);
                Route::post('profile/password/update', [User\ProfileController::class, 'passwordUpdate']);
                Route::get('profile/notifications-statistic',       [User\ProfileController::class, 'notificationStatistic']);

               
                Route::post('addresses/default/{id}', [User\AddressController::class, 'setDefaultAddress']);
                Route::post('addresses/active/{id}', [User\AddressController::class, 'setActiveAddress']);
                Route::apiResource('addresses', User\AddressController::class);

                Route::post('orders/review/{id}', [User\OrderController::class, 'addOrderReview']);
                Route::get('orders/paginate', [User\OrderController::class, 'paginate']);
                Route::get('orders-template/paginate', [User\OrderController::class, 'orderTemplate']);
                Route::put('orders-template/{id}', [User\OrderController::class, 'updateOrderTemplate']);
                Route::post('orders/{id}/status/change', [User\OrderController::class, 'orderStatusChange']);
                Route::apiResource('orders', User\OrderController::class);

                Route::get('/invites/paginate', [User\InviteController::class, 'paginate']);
                Route::post('/shop/invitation/{uuid}/link', [User\InviteController::class, 'create']);
                Route::delete('/shop/invitation/{id}', [User\InviteController::class, 'delete']);

                Route::post('/wallet/withdraw', [User\WalletController::class, 'store']);
                Route::post('/wallet/share', [User\WalletController::class, 'share']);
                Route::get('/wallet/histories', [User\WalletController::class, 'walletHistories']);
                Route::post('/wallet/billing', [User\WalletController::class, 'store']);

                Route::post('/wallet/history/{uuid}/status/change', [User\WalletController::class, 'changeStatus']);

                /* Transaction */
                Route::get('transactions/paginate', [User\TransactionController::class, 'paginate']);
                Route::get('transactions/{id}', [User\TransactionController::class, 'show']);

                /* Shop */
                Route::post('shops', [User\ShopController::class, 'store']);

                /* Ticket */
                Route::get('tickets/paginate', [User\TicketController::class, 'paginate']);
                Route::apiResource('tickets', User\TicketController::class);

                /* Export */
                Route::get('export/order/{id}/pdf', [User\ExportController::class, 'orderExportPDF']);

                /* Branch */
                Route::get('branch/paginate', [User\BranchController::class, 'index']);
                Route::get('branch/{id}', [User\BranchController::class, 'index']);

                Route::post('cart/insert-product', [User\CartController::class, 'insertProducts']);
                Route::post('cart', [User\CartController::class, 'store']);
                Route::post('cart/open', [User\CartController::class, 'openCart']);
                Route::delete('cart/{id}', [User\CartController::class, 'delete']);
                Route::delete('cart/product/{cart_detail_id}', [User\CartController::class, 'cartProductDelete']);
                Route::delete('cart/member/{user_cart_uuid}', [User\CartController::class, 'userCartDelete']);
                Route::get('cart', [User\CartController::class, 'get']);
                Route::post('cart/calculate/{id}', [User\CartController::class, 'cartCalculate']);
                Route::post('cart/status/{user_cart_uuid}', [User\CartController::class, 'statusChange']);

                Route::post('update/notifications',                 [User\ProfileController::class, 'notificationsUpdate']);
                Route::get('notifications',                         [User\ProfileController::class, 'notifications']);

                Route::post('deliveryman/review/{order_id}', [User\DeliveryManController::class, 'addDeliveryManReview']);

                Route::apiResource('parcel-orders',       User\ParcelOrderController::class);
                Route::post('parcel-orders/{id}/status/change',      [User\ParcelOrderController::class, 'orderStatusChange']);
                Route::post('parcel-orders/deliveryman-review/{id}', [User\ParcelOrderController::class, 'addDeliverymanReview']);

                Route::apiResource('refund', User\RefundController::class);

                Route::post('orders/deliveryman-review/{id}', [User\OrderController::class, 'addDeliverymanReview']);

                Route::post('become-deliveryman',[User\DeliveryManController::class,'becomeDeliveryman']);

                Route::get('my-gift-carts',[User\GiftCartController::class,'myGiftCarts']);


                Route::apiResource('wallet/request', User\WalletRequestController::class);
                Route::post('wallet/request/status/{id}', [User\WalletRequestController::class,'changeStatus']);

                // Payments
                Route::get('order-stripe-process', [Payment\StripeController::class, 'orderProcessTransaction']);
                Route::get('subscription-stripe-process', [Payment\StripeController::class, 'subscriptionProcessTransaction']);

                Route::get('order-paystack-process', [Payment\PayStackController::class, 'orderProcessTransaction']);
                Route::get('subscription-paystack-process', [Payment\PayStackController::class, 'subscriptionProcessTransaction']);

                Route::get('order-paypal-process', [Payment\PayPalController::class, 'orderProcessTransaction']);
                Route::get('subscription-paypal-process', [Payment\PayPalController::class, 'subscriptionProcessTransaction']);

                Route::get('order-paytabs-process', [Payment\PayTabsController::class, 'orderProcessTransaction']);
                Route::get('subscription-paytabs-process', [Payment\PayTabsController::class, 'subscriptionProcessTransaction']);

                Route::get('order-flw-process', [Payment\FlutterWaveController::class, 'orderProcessTransaction']);
                Route::get('subscription-flw-process', [Payment\FlutterWaveController::class, 'subscriptionProcessTransaction']);

                Route::get('order-razorpay-process', [Payment\RazorPayController::class, 'orderProcessTransaction']);
                Route::get('subscription-razorpay-process', [Payment\RazorPayController::class, 'subscriptionProcessTransaction']);


            });

            // DELIVERYMAN BLOCK
            Route::group(['prefix' => 'deliveryman', 'middleware' => ['role:deliveryman'], 'as' => 'deliveryman.'], function () {
                Route::get('orders/paginate', [Deliveryman\OrderController::class, 'paginate']);
                Route::get('orders/{id}', [Deliveryman\OrderController::class, 'show']);
                Route::post('order/{id}/status/update', [Deliveryman\OrderController::class, 'orderStatusUpdate']);
                Route::get('statistics/count', [Deliveryman\DashboardController::class, 'countStatistics']);
                Route::post('orders/{id}/review', [Deliveryman\OrderController::class, 'addReviewByDeliveryman']);
                Route::post('orders/{id}/current', [Deliveryman\OrderController::class, 'setCurrent']);

                Route::post('settings', [Deliveryman\DeliveryManSettingController::class, 'store']);
                Route::post('settings/location', [Deliveryman\DeliveryManSettingController::class, 'updateLocation']);
                Route::post('settings/online', [Deliveryman\DeliveryManSettingController::class, 'online']);
                Route::get('settings', [Deliveryman\DeliveryManSettingController::class, 'show']);
                Route::post('order/{id}/attach/me', [Deliveryman\OrderController::class, 'orderDeliverymanUpdate']);

                /* Payouts */
                Route::apiResource('payouts', Deliveryman\PayoutController::class);

                Route::delete('payouts/delete', [Deliveryman\PayoutController::class, 'destroy']);

                /* Report Orders */
                Route::get('order/report', [Deliveryman\OrderController::class, 'report']);

                Route::get('parcel-orders/paginate',            [Deliveryman\ParcelOrderController::class,  'paginate']);
                Route::post('parcel-orders/{id}/status/update', [Deliveryman\ParcelOrderController::class,  'orderStatusUpdate']);
                Route::post('parcel-order/{id}/current',        [Deliveryman\ParcelOrderController::class,  'setCurrent']);
                Route::post('parcel-order/{id}/attach/me',      [Deliveryman\ParcelOrderController::class,  'orderDeliverymanUpdate']);

                Route::get('debit-orders',      [Deliveryman\OrderController::class,  'debitOrderTransactions']);
                Route::put('debit-orders/{order_id}/status/change',      [Deliveryman\OrderController::class,  'debitOrderTransactionStatusChange']);

            });

            // SELLER BLOCK
            Route::group(['prefix' => 'seller', 'middleware' => ['role:seller|moderator', 'check.shop'], 'as' => 'seller.'], function () {
                /* Dashboard */
                Route::get('statistics/count', [Seller\DashboardController::class, 'countStatistics']);
                Route::get('statistics/customer/top', [Seller\DashboardController::class, 'topCustomersStatistics']);
                Route::get('statistics/products/top', [Seller\DashboardController::class, 'topProductsStatistics']);
                Route::get('statistics/orders/sales', [Seller\DashboardController::class, 'ordersSalesStatistics']);
                Route::get('statistics/orders/count', [Seller\DashboardController::class, 'ordersCountStatistics']);

                /* Shop Brand */
                Route::group(['prefix' => 'shop'], function () {
                    Route::get('/brand/all-brand', [Seller\ShopBrandController::class, 'allBrand']);
                    Route::get('brand', [Seller\ShopBrandController::class, 'index']);
                    Route::get('brands/paginate', [Seller\ShopBrandController::class, 'paginate']);
                    Route::get('brand/{id}', [Seller\ShopBrandController::class, 'show']);

                    Route::post('brand', [Seller\ShopBrandController::class, 'store']);
                    Route::put('brand/update', [Seller\ShopBrandController::class, 'update']);
                    Route::delete('brand', [Seller\ShopBrandController::class, 'destroy']);

                    /* Shop Category */
                    Route::get('category/children', [Seller\ShopCategoryController::class, 'children']);
                    Route::get('category/all-category', [Seller\ShopCategoryController::class, 'allCategory']);
                    Route::post('category', [Seller\ShopCategoryController::class, 'store']);
                    Route::put('category/update', [Seller\ShopCategoryController::class, 'update']);
                    Route::get('category', [Seller\ShopCategoryController::class, 'index']);
                    Route::get('category/{id}', [Seller\ShopCategoryController::class, 'show']);
                    Route::delete('category', [Seller\ShopCategoryController::class, 'destroy']);

                    /* Shop Product */
                    Route::get('product/export', [Seller\ShopProductController::class, 'fileExport']);
                    Route::get('product/all-product', [Seller\ShopProductController::class, 'allProduct']);
                    Route::get('product/getById/{uuid}', [Seller\ShopProductController::class, 'getByUuid']);
                    Route::post('product', [Seller\ShopProductController::class, 'store']);
                    Route::put('product/{id}', [Seller\ShopProductController::class, 'update']);
                    Route::get('product', [Seller\ShopProductController::class, 'index']);
                    Route::get('product/select-paginate', [Seller\ShopProductController::class, 'selectProducts']);
                    Route::get('product/{id}', [Seller\ShopProductController::class, 'show']);
                    Route::post('product/import', [Seller\ShopProductController::class, 'fileImport']);
                    Route::delete('product', [Seller\ShopProductController::class, 'destroy']);

                });

                /* Delivery Zones */
                Route::apiResource('delivery-zones', Seller\DeliveryZoneController::class);
                Route::delete('delivery-zones/delete', [Seller\DeliveryZoneController::class, 'destroy']);

                /* Units */
                Route::get('units/paginate', [Seller\UnitController::class, 'paginate']);
                Route::get('units/{id}', [Seller\UnitController::class, 'show']);

                /* Seller Shop */
                Route::get('shops', [Seller\ShopController::class, 'shopShow']);
                Route::put('shops', [Seller\ShopController::class, 'shopUpdate']);
                Route::post('shops/visibility/status', [Seller\ShopController::class, 'setVisibilityStatus']);
                Route::post('shops/working/status', [Seller\ShopController::class, 'setWorkingStatus']);

                /* Seller Categories */
                Route::get('categories/paginate', [Seller\ShopCategoryController::class, 'paginate']);
                Route::get('categories/paginate/children', [Seller\ShopCategoryController::class, 'childrenCategory']);
                Route::get('categories/select-paginate', [Seller\ShopCategoryController::class, 'selectPaginate']);

                /* Seller Payments */
                Route::get('payments/paginate', [Seller\PaymentController::class, 'paginate']);

                /* Seller Product */
                Route::get('products/paginate', [Seller\ProductController::class, 'paginate']);
                Route::get('products/search', [Seller\ProductController::class, 'productsSearch']);
                Route::post('products/{uuid}/properties', [Seller\ProductController::class, 'addProductProperties']);
                Route::post('products/export', [Seller\ProductController::class, 'fileExport']);
                Route::post('products/{uuid}/active', [Seller\ProductController::class, 'setActive']);
                Route::apiResource('products', Seller\ProductController::class);

                /* Seller Coupon */
                Route::get('coupons/paginate', [Seller\CouponController::class, 'paginate']);
                Route::apiResource('coupons', Seller\CouponController::class);
                Route::delete('coupons', [Seller\CouponController::class, 'destroy']);

                /* Seller Shop Users */
                Route::get('shop/users/paginate', [Seller\UserController::class, 'shopUsersPaginate']);
                Route::get('shop/users/role/deliveryman', [Seller\UserController::class, 'getDeliveryman']);
                Route::get('shop/users/{uuid}', [Seller\UserController::class, 'shopUserShow']);

                /* Seller Users */
                Route::get('users/paginate', [Seller\UserController::class, 'paginate']);
                Route::get('users/{uuid}', [Seller\UserController::class, 'show']); 
                Route::post('users', [Seller\UserController::class, 'store']);
                Route::post('users/{uuid}/change/status', [Seller\UserController::class, 'setUserActive']);
                Route::post('users/{uuid}/address', [Seller\UserController::class, 'userAddressCreate']);

                /* Seller Invite */
                Route::get('shops/invites/paginate', [Seller\InviteController::class, 'paginate']);
                Route::post('/shops/invites/{id}/status/change', [Seller\InviteController::class, 'changeStatus']);

                /* Seller Coupon */
                Route::get('discounts/paginate', [Seller\DiscountController::class, 'paginate']);
                Route::post('discounts/{id}/active/status', [Seller\DiscountController::class, 'setActiveStatus']);
                Route::apiResource('discounts', Seller\DiscountController::class)->except('index');
                Route::delete('discounts', [Seller\DiscountController::class, 'destroy']);

                /* Report Orders */
                Route::get('orders/report', [Seller\OrderController::class, 'orderChartPaginate']);
                Route::get('orders/report/chart', [Seller\OrderController::class, 'reportChart']);
                Route::get('orders/report/paginate', [Seller\OrderController::class, 'reportChartPaginate']);
                /* Report Revenues */
                Route::get('orders/revenue/report', [Seller\OrderController::class, 'revenueReport']);

                /* Seller Order */
                Route::get('order/calculate/products', [Seller\OrderController::class, 'calculateOrderProducts']);
                Route::get('orders/paginate', [Seller\OrderController::class, 'paginate']);
                Route::post('order/{id}/deliveryman', [Seller\OrderController::class, 'orderDetailDeliverymanUpdate']);
                Route::post('order/{id}/status', [Seller\OrderController::class, 'orderStatusUpdate']);
                Route::get('orders/get-by-status', [Seller\OrderController::class, 'getByStatus']);
                Route::apiResource('orders', Seller\OrderController::class)->except('index');

                /* Seller Deliveries */
                Route::post('deliveries/{id}/active/status', [Seller\DeliveryController::class, 'setActive']);
                Route::get('deliveries/types', [Seller\DeliveryController::class, 'deliveryTypes']);
                Route::apiResource('deliveries', Seller\DeliveryController::class);

                /* Seller Subscription */
                Route::get('subscriptions', [Seller\SubscriptionController::class, 'index']);
                Route::post('subscriptions/{id}/attach', [Seller\SubscriptionController::class, 'subscriptionAttach']);

                /* Recipe */
                Route::apiResource('recipe', Seller\RecipeController::class);
                Route::delete('recipe', [Seller\RecipeController::class, 'destroy']);
                Route::post('recipe/status/{id}', [Seller\RecipeController::class, 'statusChange']);

                /* Payment */
                Route::get('payment/all-payment', [Seller\ShopPaymentController::class, 'allPayment']);
                Route::apiResource('payment', Seller\ShopPaymentController::class);
                Route::delete('payment', [Seller\ShopPaymentController::class, 'destroy']);

                /* Recipe Category*/
                Route::apiResource('recipe-category', Seller\RecipeCategoryController::class);

                /* Banner */
                Route::apiResource('banner', Seller\BannerController::class);
                Route::delete('banner', [Seller\BannerController::class, 'destroy']);
                Route::post('banner/active/{id}', [Seller\BannerController::class, 'setActiveBanner']);

                /* Branch */
                Route::apiResource('branch', Seller\BranchController::class);
                Route::delete('branch', [Seller\BranchController::class, 'destroy']);

                /* Bonus Product */
                Route::apiResource('bonus-product', Seller\BonusProductController::class);
                Route::delete('bonus-product', [Seller\BonusProductController::class, 'destroy']);
                Route::post('bonus-product/status/{id}', [Seller\BonusProductController::class, 'statusChange']);

                /* Bonus Shop */
                Route::apiResource('bonus-shop', Seller\BonusShopController::class);
                Route::delete('bonus-shop', [Seller\BonusShopController::class, 'destroy']);
                Route::post('bonus-shop/status/{id}', [Seller\BonusShopController::class, 'statusChange']);

                /* Refund */
                Route::get('refund/statistics', [Seller\RefundController::class, 'statistics']);
                Route::get('refund/export', [Seller\RefundController::class, 'export']);
                Route::post('refund/import', [Seller\RefundController::class, 'import']);
                Route::apiResource('refund', Seller\RefundController::class);

                /* Report Overviews */
                Route::get('overview/carts', [Seller\OrderController::class, 'overviewCarts']);
                Route::get('overview/products', [Seller\OrderController::class, 'overviewProducts']);
                Route::get('overview/categories', [Seller\OrderController::class, 'overviewCategories']);

                /* Report Products */
                Route::get('products/report/chart', [Seller\ProductController::class, 'reportChart']);
                Route::get('products/report/paginate', [Seller\ProductController::class, 'reportPaginate']);

                /* Report Categories */
                Route::get('categories/report/chart', [Seller\ShopCategoryController::class, 'reportChart']);
                Route::get('categories/report/paginate', [Seller\ShopCategoryController::class, 'reportPaginate']);

                /* Shop Closed Days */
                Route::apiResource('shop-closed-dates', Seller\ShopClosedDateController::class)
                    ->except('store');
                Route::delete('shop-closed-dates/delete', [Seller\ShopClosedDateController::class, 'destroy']);

                /* Shop Working Days */
                Route::apiResource('shop-working-days', Seller\ShopWorkingDayController::class)
                    ->except('store');
                Route::delete('shop-working-days/delete', [Seller\ShopWorkingDayController::class, 'destroy']);

                /* Payouts */
                Route::apiResource('payouts', Seller\PayoutController::class);

                Route::post('payouts/{id}/status', [Seller\PayoutController::class, 'statusChange']);

                Route::delete('payouts/delete', [Seller\PayoutController::class, 'destroy']);

                /* Reviews */
                Route::get('reviews/paginate', [Seller\ReviewController::class, 'paginate']);
                Route::apiResource('reviews', Seller\ReviewController::class)->only('show');

                /* Transaction */
                Route::get('transactions/paginate', [Seller\TransactionController::class, 'paginate']);
                Route::get('transactions/{id}', [Seller\TransactionController::class, 'show']);

                Route::get('shop-tags', [Seller\ShopTagController::class, 'index']);

                Route::get('sales-history',             [Seller\HistoryController::class, 'history']);
                Route::get('sales-cards',               [Seller\HistoryController::class, 'cards']);
                Route::get('sales-main-cards',          [Seller\HistoryController::class, 'mainCards']);
                Route::get('sales-chart',               [Seller\HistoryController::class, 'chart']);
                Route::get('sales-statistic',           [Seller\HistoryController::class, 'statistic']);

                Route::get('debit-orders',      [Seller\OrderController::class,  'debitOrderTransactions']);
                Route::put('debit-orders/{order_id}/status/change',      [Seller\OrderController::class,  'debitOrderTransactionStatusChange']);

                Route::apiResource('warehouse',Seller\WarehouseController::class);
                Route::post('warehouse/export',[Seller\WarehouseController::class,'fileExport']);

            });

            // ADMIN BLOCK
            Route::group(['prefix' => 'admin', 'middleware' => ['role:admin|manager'], 'as' => 'admin.'], function () {
                /* Dashboard */
                Route::get('statistics/count', [Admin\DashboardController::class, 'countStatistics']);
                Route::get('statistics/sum', [Admin\DashboardController::class, 'sumStatistics']);
                Route::get('statistics/customer/top', [Admin\DashboardController::class, 'topCustomersStatistics']);
                Route::get('statistics/products/top', [Admin\DashboardController::class, 'topProductsStatistics']);
                Route::get('statistics/orders/sales', [Admin\DashboardController::class, 'ordersSalesStatistics']);
                Route::get('statistics/orders/count', [Admin\DashboardController::class, 'ordersCountStatistics']);

                /* Terms & Condition */
                Route::post('term', [Admin\TermsController::class, 'store']);
                Route::get('term', [Admin\TermsController::class, 'show']);
                Route::put('term/{id}', [Admin\TermsController::class, 'update']);

               
                /* Privacy & Policy */
                Route::post('policy', [Admin\PrivacyPolicyController::class, 'store']);
                Route::get('policy', [Admin\PrivacyPolicyController::class, 'show']);
                Route::put('policy/{id}', [Admin\PrivacyPolicyController::class, 'update']);
                
                /* Reviews */
                Route::get('reviews/paginate', [Admin\ReviewController::class, 'paginate']);
                Route::apiResource('reviews', Admin\ReviewController::class);
                Route::delete('reviews', [Admin\ReviewController::class,'destroy']);

                /* Languages */
                Route::get('languages/default', [Admin\LanguageController::class, 'getDefaultLanguage']);
                Route::post('languages/default/{id}', [Admin\LanguageController::class, 'setDefaultLanguage']);
                Route::get('languages/active', [Admin\LanguageController::class, 'getActiveLanguages']);
                Route::post('languages/{id}/image/delete', [Admin\LanguageController::class, 'imageDelete']);
                Route::apiResource('languages', Admin\LanguageController::class);

                /* Languages */
                Route::get('currencies/default', [Admin\CurrencyController::class, 'getDefaultCurrency']);
                Route::post('currencies/default/{id}', [Admin\CurrencyController::class, 'setDefaultCurrency']);
                Route::get('currencies/active', [Admin\CurrencyController::class, 'getActiveCurrencies']);
                Route::apiResource('currencies', Admin\CurrencyController::class);

                /* Categories */
                Route::post('categories/{uuid}/image/delete', [Admin\CategoryController::class, 'imageDelete']);
                Route::get('categories/search', [Admin\CategoryController::class, 'categoriesSearch']);
                Route::get('categories/select-paginate', [Admin\CategoryController::class, 'selectPaginate']);
                Route::get('categories/paginate', [Admin\CategoryController::class, 'paginate']);
                Route::get('categories/export', [Admin\CategoryController::class, 'fileExport']);
                Route::post('categories/import', [Admin\CategoryController::class, 'fileImport']);
                Route::delete('categories', [Admin\CategoryController::class, 'destroy']);
                Route::apiResource('categories', Admin\CategoryController::class);

                /* Brands */
                Route::post('brands/{uuid}/image/delete', [Admin\BrandController::class, 'imageDelete']);
                Route::get('brands/paginate', [Admin\BrandController::class, 'paginate']);
                Route::get('brands/search', [Admin\BrandController::class, 'brandsSearch']);
                Route::get('brands/export', [Admin\BrandController::class, 'fileExport']);
                Route::post('brands/import', [Admin\BrandController::class, 'fileImport']);
                Route::apiResource('brands', Admin\BrandController::class);
                Route::delete('brands', [Admin\BrandController::class, 'destroy']);

                /* Brands */
                Route::get('banners/paginate', [Admin\BannerController::class, 'paginate']);
                Route::post('banners/active/{id}', [Admin\BannerController::class, 'setActiveBanner']);
                Route::delete('banners', [Admin\BannerController::class, 'destroy']);
                Route::apiResource('banners', Admin\BannerController::class);

                /* Units */
                Route::get('units/paginate', [Admin\UnitController::class, 'paginate']);
                Route::post('units/active/{id}', [Admin\UnitController::class, 'setActiveUnit']);
                Route::delete('units', [Admin\UnitController::class, 'destroy']);
                Route::apiResource('units', Admin\UnitController::class);

                /* Shops */

                Route::get('shops/search', [Admin\ShopController::class, 'shopsSearch']);
                Route::get('shops/paginate', [Admin\ShopController::class, 'paginate']);
                Route::get('shops/nearby', [Admin\ShopController::class, 'nearbyShops']);
                Route::post('shops/{uuid}/image/delete', [Admin\ShopController::class, 'imageDelete']);
                Route::post('shops/{uuid}/status/change', [Admin\ShopController::class, 'statusChange']);
                Route::post('shops/export', [Admin\ShopController::class, 'fileExport']);
                Route::delete('shops', [Admin\ShopController::class, 'destroy']);
                Route::apiResource('shops', Admin\ShopController::class);

                /* Products */
                Route::get('products/paginate', [Admin\ProductController::class, 'paginate']);
                Route::get('products/search', [Admin\ProductController::class, 'productsSearch']);
                Route::post('products/{uuid}/properties', [Admin\ProductController::class, 'addProductProperties']);
                Route::post('products/{uuid}/active', [Admin\ProductController::class, 'setActive']);
                Route::get('products/export', [Admin\ProductController::class, 'fileExport']);
                Route::post('products/import', [Admin\ProductController::class, 'fileImport']);
                Route::post('products/delete/all', [Admin\ProductController::class, 'deleteAll']);
                Route::apiResource('products', Admin\ProductController::class);

                /* Point */
                Route::get('points/paginate', [Admin\PointController::class, 'paginate']);
                Route::post('points/{id}/active', [Admin\PointController::class, 'setActive']);
                Route::delete('points', [Admin\PointController::class, 'destroy']);
                Route::apiResource('points', Admin\PointController::class);

                Route::get('gift/notification', [Admin\GiftNotificationController::class, 'giftNotification']);

                /* gift and caseback history */
                Route::get('gift/history', [Admin\GiftCashbackController::class, 'GiftHistory']);
                Route::get('cashback/history', [Admin\GiftCashbackController::class, 'CashbackHistory']);

                /* Orders */
                Route::get('orders/paginate', [Admin\OrderController::class, 'paginate']);
                Route::get('orders/get-by-status', [Admin\OrderController::class, 'getByStatus']);
                Route::apiResource('orders', Admin\OrderController::class);

                /* Order Details */
                Route::get('order/calculate/products', [Admin\OrderController::class, 'calculateOrderProducts']);
                Route::post('order/{id}/deliveryman', [Admin\OrderController::class, 'orderDeliverymanUpdate']);
                Route::post('order/{id}/status', [Admin\OrderController::class, 'orderStatusUpdate']);
                Route::get('order/export', [Admin\OrderController::class, 'fileExport']);

                /* Users Address */
                Route::post('/users/{uuid}/addresses', [Admin\UserAddressController::class, 'store']);

                /* Users */
                
      
                Route::post('users/delete/all', [Admin\UserController::class, 'deleteAll']);
                Route::get('users/search', [Admin\UserController::class, 'usersSearch']);
                Route::get('users/paginate', [Admin\UserController::class, 'paginate']);
                Route::post('users/{uuid}/role/update', [Admin\UserController::class, 'updateRole']);
                Route::get('users/{uuid}/wallets/history', [Admin\UserController::class, 'walletHistories']);
                Route::post('users/{uuid}/wallets', [Admin\UserController::class, 'topUpWallet']);
                Route::post('users/{uuid}/active', [Admin\UserController::class, 'setActive']);
                Route::put('users/{uuid}/password', [Admin\UserController::class, 'passwordUpdate']);
                Route::put('users/{uuid}', [Admin\UserController::class,'update']);
                Route::apiResource('users', Admin\UserController::class);
                Route::get('roles', Admin\RoleController::class);

                Route::get('/point/histories', [User\WalletController::class, 'pointHistories']);

                /* Users Wallet Histories */
                Route::get('/wallet/histories/paginate', [Admin\WalletHistoryController::class, 'paginate']);
                Route::post('/wallet/history/{uuid}/status/change', [Admin\WalletHistoryController::class, 'changeStatus']);

                /* Subscriptions */
                Route::apiResource('subscriptions', Admin\SubscriptionController::class);

                /* Payments */
                Route::post('payments/{id}/active/status', [Admin\PaymentController::class, 'setActive']);
                Route::apiResource('payments', Admin\PaymentController::class)->except('store', 'delete');

                /* SMS Gateways */
                Route::post('sms-gateways/{id}/active/status', [Admin\SMSGatewayController::class, 'setActive']);
                Route::apiResource('sms-gateways', Admin\SMSGatewayController::class)->except('store', 'delete');

                /* Translations */
                Route::get('translations/paginate', [Admin\TranslationController::class, 'paginate']);
                Route::apiResource('translations', Admin\TranslationController::class);

                /* Transaction */
                Route::get('transactions/paginate', [Admin\TransactionController::class, 'paginate']);
                Route::get('transactions/{id}', [Admin\TransactionController::class, 'show']);

                Route::get('tickets/paginate', [Admin\TicketController::class, 'paginate']);
                Route::post('tickets/{id}/status', [Admin\TicketController::class, 'setStatus']);
                Route::get('tickets/statuses', [Admin\TicketController::class, 'getStatuses']);
                Route::apiResource('tickets', Admin\TicketController::class);

                /* Deliveries */
                Route::get('delivery/types', [Admin\DeliveryController::class, 'deliveryTypes']);
                Route::apiResource('deliveries', Admin\DeliveryController::class);

                /* FAQS */
                Route::get('faqs/paginate', [Admin\FAQController::class, 'paginate']);
                Route::post('faqs/{uuid}/active/status', [Admin\FAQController::class, 'setActiveStatus']);
                Route::apiResource('faqs', Admin\FAQController::class)->except('index');

                /* Blogs */
                Route::get('blogs/paginate', [Admin\BlogController::class, 'paginate']);
                Route::post('blogs/{uuid}/publish', [Admin\BlogController::class, 'blogPublish']);
                Route::post('blogs/{uuid}/active/status', [Admin\BlogController::class, 'setActiveStatus']);
                Route::delete('blogs', [Admin\BlogController::class, 'destroy']);
                Route::apiResource('blogs', Admin\BlogController::class)->except('index');

                /* Settings */
                Route::get('settings/system/information', [Admin\SettingController::class, 'systemInformation']);
                Route::apiResource('settings', Admin\SettingController::class);
                Route::post('backup/history', [Admin\BackupController::class, 'download']);
                Route::get('backup/history', [Admin\BackupController::class, 'histories']);

                // Auto updates
                Route::post('/project-upload', [Admin\ProjectController::class, 'projectUpload']);
                Route::post('/project-update', [Admin\ProjectController::class, 'projectUpdate']);

                /* Recipe Category*/
                Route::apiResource('recipe-category', Admin\RecipeCategoryController::class);
                Route::delete('recipe-category', [Admin\RecipeCategoryController::class, 'destroy']);
                Route::post('recipe-category/status/{id}', [Admin\RecipeCategoryController::class, 'statusChange']);

                /* Shop group */
                Route::apiResource('groups', Admin\GroupController::class);
                Route::delete('groups', [Admin\GroupController::class, 'destroy']);
                Route::post('groups/active/{id}', [Admin\GroupController::class, 'statusChange']);

                /* Refund */
                Route::get('refund/statistics', [Admin\RefundController::class, 'statistics']);
                Route::get('refund/export', [Admin\RefundController::class, 'export']);
                Route::post('refund/import', [Admin\RefundController::class, 'import']);
                Route::apiResource('refund', Admin\RefundController::class);

                /* Email Setting */
                Route::apiResource('email-settings', Admin\EmailSettingController::class);
                Route::delete('email-settings', [Admin\EmailSettingController::class, 'destroy']);
                Route::get('email-settings/set-active/{id}', [Admin\EmailSettingController::class, 'setActive']);

                /* Email Templates */

                Route::get('email-templates/types', [Admin\EmailTemplateController::class, 'types']);
                Route::apiResource('email-templates', Admin\EmailTemplateController::class);
                Route::delete('email-templates', [Admin\EmailTemplateController::class, 'destroy']);

                /* Email Subscriptions */
                Route::get('email-subscriptions', [Admin\SubscriptionController::class, 'emailSubscriptions']);

                /* Report Products */
                Route::get('products/report/chart', [Admin\ProductController::class, 'reportChart']);
                Route::get('products/report/paginate', [Admin\ProductController::class, 'reportPaginate']);

                /* Report Orders */
                Route::get('orders/report/chart', [Admin\OrderController::class, 'reportChart']);
                Route::get('orders/report/paginate', [Admin\OrderController::class, 'reportChartPaginate']);

                /* Report Revenues */
                Route::get('revenue/report', [Admin\OrderController::class, 'revenueReport']);

                /* Report Overviews */
                Route::get('overview/carts', [Admin\OrderController::class, 'overviewCarts']);
                Route::get('overview/products', [Admin\OrderController::class, 'overviewProducts']);
                Route::get('overview/categories', [Admin\OrderController::class, 'overviewCategories']);

                /* Report Categories */
                Route::get('categories/report/chart', [Admin\CategoryController::class, 'reportChart']);
                Route::get('categories/report/paginate', [Admin\CategoryController::class, 'reportPaginate']);

                /* Delivery Zones */
                Route::apiResource('delivery-zones', Admin\DeliveryZoneController::class);
                Route::delete('delivery-zones/delete', [Admin\DeliveryZoneController::class, 'destroy']);
//                Route::get('delivery-zones/drop/all',          [Admin\DeliveryZoneController::class, 'dropAll']);
//                Route::get('delivery-zones/restore/all',       [Admin\DeliveryZoneController::class, 'restoreAll']);
//                Route::get('delivery-zones/truncate/db',       [Admin\DeliveryZoneController::class, 'truncate']);

                Route::get('deliveryman/paginate', [Admin\DeliveryManController::class, 'paginate']);
                Route::get('deliveryman-settings/paginate', [Admin\DeliveryManSettingController::class, 'paginate']);
                Route::delete('deliveryman-settings/delete', [Admin\DeliveryManSettingController::class, 'destroy']);

                Route::apiResource('deliveryman-settings', Admin\DeliveryManSettingController::class)
                    ->except('index', 'destroy');

                /* Shop Closed Days */
                Route::get('shop-closed-dates/paginate', [Admin\ShopClosedDateController::class, 'paginate']);

                Route::apiResource('shop-closed-dates', Admin\ShopClosedDateController::class)
                    ->except('index', 'store');
                Route::delete('shop-closed-dates/delete', [Admin\ShopClosedDateController::class, 'destroy']);

                /* Shop Working Days */
                Route::get('shop-working-days/paginate', [Admin\ShopWorkingDayController::class, 'paginate']);

                Route::apiResource('shop-working-days', Admin\ShopWorkingDayController::class)
                    ->except('index', 'store');

                Route::delete('shop-working-days/delete', [Admin\ShopWorkingDayController::class, 'destroy']);

                /* Payouts */
                Route::apiResource('payouts', Admin\PayoutController::class);
                Route::post('payouts/{id}/status', [Admin\PayoutController::class, 'statusChange']);
                Route::delete('payouts/delete', [Admin\PayoutController::class, 'destroy']);

                Route::apiResource('referrals', Admin\ReferralController::class);
                Route::get('referrals/transactions/paginate', [Admin\ReferralController::class, 'transactions']);

                /* Order Statuses */
                Route::get('order-statuses', [Admin\OrderStatusController::class, 'index']);
                Route::post('order-statuses/{id}/active', [Admin\OrderStatusController::class, 'active']);

                /* Shop tags */
                Route::apiResource('shop-tags', Admin\ShopTagController::class);
                Route::delete('shop-tags/delete', [Admin\ShopTagController::class, 'destroy']);

                /* Shop bonus */
                Route::get('bonuses', [Admin\ShopBonusController::class, 'index']);

                /* PaymentPayload tags */
                Route::apiResource('payment-payloads', Admin\PaymentPayloadController::class);
                Route::delete('payment-payloads/delete', [Admin\PaymentPayloadController::class, 'destroy']);

                /* Parcel Orders */
                Route::get('parcel-order/export',            [Admin\ParcelOrderController::class, 'fileExport']);
                Route::post('parcel-order/import',           [Admin\ParcelOrderController::class, 'fileImport']);
                Route::post('parcel-order/{id}/deliveryman', [Admin\ParcelOrderController::class, 'orderDeliverymanUpdate']);
                Route::post('parcel-order/{id}/status',      [Admin\ParcelOrderController::class, 'orderStatusUpdate']);
                Route::apiResource('parcel-orders',       Admin\ParcelOrderController::class);
                Route::delete('parcel-orders/delete',        [Admin\ParcelOrderController::class, 'destroy']);

                /* Parcel Options */
                Route::apiResource('parcel-options',    Admin\ParcelOptionController::class);
                Route::delete('parcel-options/delete',           [Admin\ParcelOptionController::class, 'destroy']);
                Route::get('parcel-options/drop/all',            [Admin\ParcelOptionController::class, 'dropAll']);
                Route::get('parcel-options/restore/all',         [Admin\ParcelOptionController::class, 'restoreAll']);
                Route::get('parcel-options/truncate/db',         [Admin\ParcelOptionController::class, 'truncate']);

                /* Parcel Order Setting */
                Route::apiResource('parcel-order-settings',    Admin\ParcelOrderSettingController::class);
                Route::delete('parcel-order-settings/delete',    [Admin\ParcelOrderSettingController::class, 'destroy']);
                Route::get('parcel-order-settings/drop/all',     [Admin\ParcelOrderSettingController::class, 'dropAll']);
                Route::get('parcel-order-settings/restore/all',  [Admin\ParcelOrderSettingController::class, 'restoreAll']);
                Route::get('parcel-order-settings/truncate/db',  [Admin\ParcelOrderSettingController::class, 'truncate']);

                /* Debit Orders */
                Route::get('debit-orders',      [Admin\OrderController::class,  'debitOrderTransactions']);
                Route::put('debit-orders/{order_id}/status/change',      [Admin\OrderController::class,  'debitOrderTransactionStatusChange']);


                /* Notifications */
                Route::apiResource('notifications', Admin\NotificationController::class);
                Route::delete('notifications/delete',   [Admin\NotificationController::class, 'destroy']);

                Route::put('orders/billing-report',      [Admin\OrderController::class,  'billingReport']);

            });

        });

    });
    Route::group(['prefix' => 'webhook'], function () {
        Route::any('paypal/payment',        [Payment\PayPalController::class,       'paymentWebHook']);
        Route::any('razorpay/payment',      [Payment\RazorPayController::class,     'paymentWebHook']);
        Route::any('stripe/payment',        [Payment\StripeController::class,       'paymentWebHook']);
        Route::any('flw/payment',           [Payment\FlutterWaveController::class,  'paymentWebHook']);
        Route::any('paystack/payment',      [Payment\PayStackController::class,     'paymentWebHook']);
    });

});
