<?php

namespace App\Repositories\BonusRepository;

use App\Models\BonusShop;
use App\Repositories\CoreRepository;

class BonusShopRepository extends CoreRepository
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
        return BonusShop::class;
    }

    public function paginate(int $perPage, $shop)
    {
        return $this->model()->with([
            'shopProduct.product.translation' => fn($q) => $q->select('id', 'product_id', 'locale', 'title')
        ])->when($shop, function ($q) use ($shop) {
            $q->where('shop_id', $shop->id);
        })->whereHas('shopProduct.product.translation')
            ->orderByDesc('id')->paginate($perPage);
    }


    /**
     * Get one brands by Identification number
     */
    public function show(int $id)
    {
        return $this->model()->with(['shopProduct.product.translation' => fn($q) => $q->select('id', 'product_id', 'locale', 'title')])->find($id);
    }

}
