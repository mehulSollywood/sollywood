<?php

namespace App\Repositories\BonusRepository;

use App\Models\BonusProduct;
use App\Repositories\CoreRepository;

class BonusProductRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();

    }

    protected function getModelClass(): string
    {
        return BonusProduct::class;
    }

    public function paginate($perPage, $shopId = null)
    {
        return $this->model()->with([
            'bonusProduct.product.translation' => fn($q) => $q->select('id', 'product_id', 'locale', 'title')])
            ->whereHas('shopProduct',function ($q) use ($shopId){
                $q->where('shop_id',$shopId);
            })
            ->whereHas('shopProduct.product.translation')
            ->whereHas('bonusProduct.product.translation')
            ->orderByDesc('id')
            ->paginate($perPage);
    }


    /**
     * Get one brands by Identification number
     */
    public function show(int $id)
    {
        return $this->model()->with([
            'shopProduct.product.translation' => fn($q) => $q->where('locale', $this->language)->select('id', 'product_id', 'locale', 'title'),
            'bonusProduct.product.translation' => fn($q) => $q->where('locale', $this->language)->select('id', 'product_id', 'locale', 'title')
        ])->find($id);
    }

}
