<?php

namespace App\Repositories\DeliveryRepository;

use App\Models\Delivery;
use App\Repositories\CoreRepository;

class DeliveryRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Delivery::class;
    }

    public function deliveriesList($shop = null, $active = null, $array = [])
    {
        return $this->model()->whereHas('translation')
            ->with('translation')
            ->filter($array)
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function deliveriesPaginate($perPage, $shop = null, $active = null, $array = [])
    {
        return $this->model()
            ->with([
                'translation'
            ])
            ->filter($array)
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function deliveryDetails($id, $shop = null)
    {
        return $this->model()
            ->with('translation')
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }

}
