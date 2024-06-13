<?php

namespace App\Repositories\BannerRepository;

use App\Models\Banner;
use App\Repositories\CoreRepository;

class BannerRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Banner::class;
    }

    public function bannerDetails($id)
    {
        return $this->model()->with([
            'translation',
            'translations'
        ])->find($id);
    }

    public function bannerPaginateSeller($perPage, $shop_id)
    {
        return $this->model()->with('translation')
            ->when(isset($shop_id),function ($q) use ($shop_id){
                $q->where('shop_id', $shop_id);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function bannersPaginateRest($perPage, $array)
    {
        return $this->model()->with('translation')
            ->when(isset($array['shop_id']),function ($q) use ($array){
                $q->where('shop_id', $array['shop_id']);
            })
            ->when(!isset($array['shop_id']),function ($q) use ($array){
                $q->where('shop_id', null);
            })
            ->whereHas('translation')
            ->where('active',true)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

}
