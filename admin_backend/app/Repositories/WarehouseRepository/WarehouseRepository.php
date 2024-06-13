<?php

namespace App\Repositories\WarehouseRepository;

use App\Models\Warehouse;
use App\Repositories\CoreRepository;

class WarehouseRepository extends CoreRepository
{

    protected function getModelClass(): string
    {
        return Warehouse::class;
    }

    public function paginate($perPage)
    {
        return $this->model()->with([
            'shopProduct.product.translation',
            'user:id,uuid,firstname,lastname,email,phone,active',
        ])
            ->orderBy('id','desc')
            ->paginate($perPage);
    }

    public function show($id)
    {
        return $this->model()->with([
            'shopProduct.product.translation',
            'user',
        ])->find($id);
    }
}
