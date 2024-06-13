<?php

namespace App\Repositories\BrandRepository;

use App\Models\Brand;
use App\Repositories\CoreRepository;

class BrandRepository extends CoreRepository
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
        return Brand::class;
    }

    public function brandsList(array $array = [])
    {
        return $this->model()
            ->filter($array)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get brands with pagination
     */
    public function brandsPaginate($perPage, $active = null, $array = [])
    {
        return $this->model()
            ->filter($array)
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->where('title', 'LIKE', "%" . $array['search'] . "%");
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get one brands by Identification number
     */
    public function brandDetails(int $id)
    {
        return $this->model()->find($id);
    }

    public function brandsSearch(string $search, $active = null)
    {

        return $this->model()
            ->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->orderByDesc('id')
            ->latest()->take(50)->get();
    }

    public function shopBrandById(int $id, int $shop_id)
    {
        return $this->model()
            ->whereHas('shopBrand', function ($q) use ($shop_id) {
                $q->where('shop_id', $shop_id);
            })->orderByDesc('id')->find($id);
    }

    //Bu methodda shop o`ziga tortib olmagan brandlar chiqadi

    public function shopBrandNonExistPaginate(int $shop_id, $array, $perPage)
    {
        $shopBrandIds = $this->model()
            ->whereHas('shopBrand', function ($q) use ($shop_id) {
                $q->where('shop_id', $shop_id);
            })->pluck('id');

        return $this->model()
            ->whereNotIn('id', $shopBrandIds)
            ->where('active', 1)
            ->filter($array)
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
