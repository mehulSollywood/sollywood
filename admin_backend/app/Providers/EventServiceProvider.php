<?php

namespace App\Providers;

use App\Events\Mails\EmailSendByTemplate;
use App\Events\Mails\SendEmailVerification;
use App\Listeners\Mails\EmailSendByTemplateListener;
use App\Listeners\Mails\SendEmailVerificationListener;
use App\Models\Brand;
use App\Models\CartDetail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\ShopTranslation;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserCart;
use App\Models\Wallet;
use App\Observers\BrandObserver;
use App\Observers\CartDetailObserver;
use App\Observers\CategoryObserver;
use App\Observers\OrderDetailObserver;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductTranslationObserver;
use App\Observers\ShopObserver;
use App\Observers\ShopProductObserver;
use App\Observers\ShopTranslationObserver;
use App\Observers\TicketObserver;
use App\Observers\UserCartObserver;
use App\Observers\UserObserver;
use App\Observers\WalletObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        SendEmailVerification::class => [
            SendEmailVerificationListener::class,
        ],
        EmailSendByTemplate::class => [
            EmailSendByTemplateListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Category::observe(CategoryObserver::class);
        Shop::observe(ShopObserver::class);
        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
        Brand::observe(BrandObserver::class);
        Ticket::observe(TicketObserver::class);
        UserCart::observe(UserCartObserver::class);
        ShopProduct::observe(ShopProductObserver::class);
        CartDetail::observe(CartDetailObserver::class);
        OrderDetail::observe(OrderDetailObserver::class);
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);
        Wallet::observe(WalletObserver::class);
        ShopTranslation::observe(ShopTranslationObserver::class);
        ProductTranslation::observe(ProductTranslationObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
