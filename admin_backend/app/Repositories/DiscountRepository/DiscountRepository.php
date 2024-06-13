<?php

namespace App\Repositories\DiscountRepository;

use App\Models\Discount;
use App\Repositories\CoreRepository;

class DiscountRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Discount::class;
    }

    public function discountsPaginate($perPage, $shop = null, $active = null, $array = [])
    {
        return $this->model()
            ->filter($array)
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderBy('id','desc')
            ->paginate($perPage);
    }

    public function discountDetails($id, $shop = null)
    {
        return $this->model()->with([
            'products.product.translation:id,product_id,locale,title'
        ])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }

}
