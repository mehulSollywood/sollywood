<?php

namespace App\Repositories\CouponRepository;

use App\Models\Coupon;
use App\Repositories\CoreRepository;

class CouponRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Coupon::class;
    }

    public function couponsList($array)
    {
        return $this->model()->filter($array)->orderByDesc('id')->get();
    }

    public function couponsPaginate($perPage, $shop = null)
    {
        return $this->model()->whereHas('translation')
            ->with([
                'translation'
            ])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function couponById(int $id, $shop = null)
    {
        return $this->model()->with([
            'shop',
            'galleries',
            'translation'
        ])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }
}
