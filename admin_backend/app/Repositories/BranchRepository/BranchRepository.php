<?php

namespace App\Repositories\BranchRepository;


use App\Models\Branch;
use App\Repositories\CoreRepository;

class BranchRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Branch::class;
    }


    public function paginate($perPage, $shop = null)
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


    public function getById(int $id, $shop = null)
    {
        return $this->model()->with([
            'translations',
            'translation'
        ])
            ->when(isset($shop), function ($q) use($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }
}
