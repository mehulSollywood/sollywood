<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderTemplate;
use App\Models\ShopProduct;
use App\Models\Transaction;
use Illuminate\Console\Command;

class OrderTemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order-template:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check order template table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $orderTemplates = OrderTemplate::where('date->start_date', '<', now())
            ->where('date->end_date', '>', now())
            ->get();

        foreach ($orderTemplates as $orderTemplate) {

            $order = Order::create($orderTemplate?->order()?->first()->toArray());

            $order->update([
                'status' => Order::NEW
            ]);

            $transaction = Transaction::where('payable_id',$orderTemplate->order_id)
                ->where('payable_type',Order::class)
                ->first()
                ?->replicate();

            $transaction->payable_id = $order->id;

            $transaction->save();

            $orderDetailIds = $orderTemplate?->order()?->first()?->orderDetails()?->pluck('id');

            $orderDetails = OrderDetail::find($orderDetailIds);

            foreach ($orderDetails as $orderDetail) {

                $shopProduct = ShopProduct::where('quantity', '>', 1)
                    ->find($orderDetail->shop_product_id);

                if ($shopProduct?->quantity < $orderDetail->quantity){
                    $order?->forceDelete();
                    $transaction?->forceDelete();
                    continue;
                }

                $shopProduct->update(['quantity' => $shopProduct?->quantity - $orderDetail->quantity ]);

                $orderDetail->replicate()->save();

                $orderDetail->update([
                    'order_id' => $order?->id
                ]);
            }
        }
    }
}
