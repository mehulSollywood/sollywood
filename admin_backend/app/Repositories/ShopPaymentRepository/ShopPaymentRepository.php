<?php

namespace App\Repositories\ShopPaymentRepository;

use App\Models\Payment;
use App\Models\ShopPayment;
use App\Repositories\CoreRepository;

class ShopPaymentRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return ShopPayment::class;
    }

    // get list product
    public function list($array)
    {
        return $this->model()->filter($array)->get();
    }

    public function paginate($perPage, $array, $shop = null)
    {
        return $this->model()->with(['payment.translation'])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($array['wallet_topup']),function ($q){
                $q->whereHas('payment',function ($q){
                    $q->where('tag','!=',Payment::TAG_CASH);
                });
            })
            ->whereHas('payment.translation')
            ->orderByDesc('id')
            ->paginate($perPage);
    }


    public function getById(int $id, $shop = null)
    {
        return $this->model()->with(['payment.translation','payment.translations'])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }
}
