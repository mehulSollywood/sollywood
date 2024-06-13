<?php

namespace App\Repositories\RefundRepository;


use App\Models\Refund;
use App\Repositories\CoreRepository;
use DB;

class RefundRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Refund::class;
    }

    // get list product
    public function paginate($collection, $shop = null, $user = null)
    {
        return $this->model->filter($collection)
            ->with([
                'user:firstname,lastname,email,phone,id',
                'order:price',
                'order.shop:id,uuid,logo_img',
                'order.shop.translation:id,locale,title,shop_id',
            ])
            ->when($user, function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->when($shop, function ($q) use ($shop) {
                $q->whereHas('order',function ($q) use ($shop){
                    $q->where('shop_id', $shop->id);
                });
            })
            ->orderByDesc('id')
            ->paginate($collection['perPage']);
    }

    public function show(int $id)
    {
        return $this->model()->with([
            'user',
            'galleries',
            'order.shop:id,uuid,logo_img',
            'order.transaction.paymentSystem.payment',
            'order.deliveryType',
            'order.orderDetails.shopProduct:id,product_id',
            'order.orderDetails.shopProduct.product:id,img',
            'order.orderDetails.shopProduct.product.translation:id,product_id,locale,title',
            'order.shop.translation:id,locale,title,shop_id',
        ])->find($id);
    }

    public function statisticsShop($shop = null)
    {
        return DB::table('refunds as r')->select(DB::raw(
            "sum(case when (r.status='pending') then 1 else 0 end) as count_pending_refunds,
                   sum(case when (r.status='canceled') then 1 else 0 end) as count_canceled_refunds,
                   sum(case when (r.status='accepted') then 1 else 0 end) as count_accepted_refunds"
        ))
            ->join('orders as o', 'o.id', '=', 'r.order_id')
            ->where('o.shop_id', $shop->id)
            ->whereNull('r.deleted_at')
            ->first();
    }

    public function statisticsAdmin($lang)
    {
        return DB::table('refunds as r')->select(DB::raw(
            "sum(case when (r.status='pending') then 1 else 0 end) as count_pending_refunds,
                   sum(case when (r.status='canceled') then 1 else 0 end) as count_canceled_refunds,
                   sum(case when (r.status='accepted') then 1 else 0 end) as count_accepted_refunds,
                   sh.id,sh_t.title
                   "
        ))
            ->join('orders as o', 'o.id', '=', 'r.order_id')
            ->join('shops as sh', 'sh.id', '=', 'o.shop_id')
            ->leftJoin('shop_translations as sh_t', 'sh.id', '=', 'sh_t.shop_id')
            ->groupBy('sh.id','sh_t.title')
            ->where('sh_t.locale',$lang)
            ->whereNull('r.deleted_at')
            ->get();
    }
}
